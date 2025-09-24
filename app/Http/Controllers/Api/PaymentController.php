<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(Payment::class, 'payment');
    }

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->with(['appointment.client:id,name', 'appointment.service:id,name'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->from_date && $request->to_date, function($q) use ($request) {
                $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($payments);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = Payment::create($request->validated());
        $payment->load(['appointment.client:id,name', 'appointment.service:id,name']);

        return $this->successResponse($payment, 'Payment created successfully', 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['appointment.client:id,name,email', 'appointment.service:id,name']);
        return $this->successResponse($payment);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());
        $payment->load(['appointment.client:id,name', 'appointment.service:id,name']);

        return $this->successResponse($payment, 'Payment updated successfully');
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return $this->successResponse(null, 'Payment deleted successfully');
    }

    public function refund(Payment $payment): JsonResponse
    {
        $this->authorize('refund', $payment);

        if ($payment->status !== 'paid') {
            return $this->errorResponse('Only paid payments can be refunded', 422);
        }

        $payment->update([
            'status' => 'refunded',
            'transaction_reference' => 'REFUND_' . time(),
        ]);

        return $this->successResponse($payment, 'Payment refunded successfully');
    }
}
