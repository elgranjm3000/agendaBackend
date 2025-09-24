<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => [
                'nullable', 
                'integer',
                Rule::exists('appointments', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'method' => ['required', 'in:cash,card,online'],
            'status' => ['sometimes', 'in:pending,paid,refunded'],
            'transaction_reference' => ['nullable', 'string', 'max:150'],
        ];
    }
}