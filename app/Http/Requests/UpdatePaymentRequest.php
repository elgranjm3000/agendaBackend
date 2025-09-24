<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => [
                'sometimes', 
                'nullable',
                'integer',
                Rule::exists('appointments', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:999999.99'],
            'method' => ['sometimes', 'in:cash,card,online'],
            'status' => ['sometimes', 'in:pending,paid,refunded'],
            'transaction_reference' => ['sometimes', 'nullable', 'string', 'max:150'],
        ];
    }
}