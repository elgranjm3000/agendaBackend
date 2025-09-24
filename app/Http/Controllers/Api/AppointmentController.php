<?php
namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Events\AppointmentCreated;
use App\Events\AppointmentUpdated;
use App\Events\AppointmentCancelled;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(Appointment::class, 'appointment');
    }

    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->with(['client:id,name', 'service:id,name,price', 'user:id,name'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->date, fn($q) => $q->whereDate('start_time', $request->date))
            ->when($request->from_date && $request->to_date, function($q) use ($request) {
                $q->whereBetween('start_time', [$request->from_date, $request->to_date]);
            })
            ->orderBy('start_time', 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::create($request->validated());
        $appointment->load(['client:id,name', 'service:id,name,price', 'user:id,name']);

        event(new AppointmentCreated($appointment));

        return $this->successResponse($appointment, 'Appointment created successfully', 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $appointment->load([
            'client:id,name,email,phone', 
            'service:id,name,price,duration_minutes', 
            'user:id,name',
            'payment:id,amount,method,status'
        ]);

        return $this->successResponse($appointment);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $oldStatus = $appointment->status;
        $appointment->update($request->validated());
        $appointment->load(['client:id,name', 'service:id,name,price', 'user:id,name']);

        if ($oldStatus !== $appointment->status) {
            if ($appointment->status === 'cancelled') {
                event(new AppointmentCancelled($appointment));
            } else {
                event(new AppointmentUpdated($appointment));
            }
        }

        return $this->successResponse($appointment, 'Appointment updated successfully');
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return $this->successResponse(null, 'Appointment deleted successfully');
    }

    public function reschedule(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('reschedule', $appointment);

        if ($appointment->status !== 'scheduled') {
            return $this->errorResponse('Only scheduled appointments can be rescheduled', 422);
        }

        $appointment->update($request->only(['start_time', 'end_time', 'user_id']));
        $appointment->load(['client:id,name', 'service:id,name,price', 'user:id,name']);

        event(new AppointmentUpdated($appointment));

        return $this->successResponse($appointment, 'Appointment rescheduled successfully');
    }

    public function cancel(Appointment $appointment): JsonResponse
    {
        $this->authorize('cancel', $appointment);

        if (!in_array($appointment->status, ['scheduled'])) {
            return $this->errorResponse('Only scheduled appointments can be cancelled', 422);
        }

        $appointment->update(['status' => 'cancelled']);

        event(new AppointmentCancelled($appointment));

        return $this->successResponse($appointment, 'Appointment cancelled successfully');
    }

    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $service = \App\Models\Service::findOrFail($request->service_id);
        $date = $request->date;

        // Get existing appointments for the user on the given date
        $existingAppointments = Appointment::where('user_id', $user->id)
            ->whereDate('start_time', $date)
            ->where('status', 'scheduled')
            ->select('start_time', 'end_time')
            ->get();

        // Generate available time slots (assuming 9 AM to 6 PM, 30-minute slots)
        $workStart = 9; // 9 AM
        $workEnd = 18; // 6 PM
        $slotDuration = 30; // 30 minutes
        $serviceDuration = $service->duration_minutes;

        $availableSlots = [];
        $currentTime = $workStart * 60; // Convert to minutes

        while ($currentTime + $serviceDuration <= $workEnd * 60) {
            $slotStart = sprintf('%02d:%02d', floor($currentTime / 60), $currentTime % 60);
            $slotEnd = sprintf('%02d:%02d', floor(($currentTime + $serviceDuration) / 60), ($currentTime + $serviceDuration) % 60);

            // Check if this slot conflicts with existing appointments
            $hasConflict = $existingAppointments->contains(function ($appointment) use ($date, $slotStart, $slotEnd) {
                $appointmentStart = $appointment->start_time->format('H:i');
                $appointmentEnd = $appointment->end_time->format('H:i');

                return ($slotStart >= $appointmentStart && $slotStart < $appointmentEnd) ||
                       ($slotEnd > $appointmentStart && $slotEnd <= $appointmentEnd) ||
                       ($slotStart <= $appointmentStart && $slotEnd >= $appointmentEnd);
            });

            if (!$hasConflict) {
                $availableSlots[] = [
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                ];
            }

            $currentTime += $slotDuration;
        }

        return $this->successResponse([
            'date' => $date,
            'user' => $user->only('id', 'name'),
            'service' => $service->only('id', 'name', 'duration_minutes'),
            'available_slots' => $availableSlots,
        ]);
    }
}
