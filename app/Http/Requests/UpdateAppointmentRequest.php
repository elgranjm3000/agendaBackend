<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'sometimes', 
                'integer',
                Rule::exists('clients', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'service_id' => [
                'sometimes', 
                'integer',
                Rule::exists('services', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'user_id' => [
                'sometimes', 
                'integer',
                Rule::exists('users', 'id')->where('company_id', auth()->user()->company_id)
            ],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'status' => ['sometimes', 'in:scheduled,completed,cancelled,no_show'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->start_time && $this->end_time && $this->user_id) {
                // Check for appointment conflicts (excluding current appointment)
                $conflicts = \App\Models\Appointment::where('user_id', $this->user_id)
                    ->where('status', 'scheduled')
                    ->where('id', '!=', $this->route('appointment')->id)
                    ->where(function ($query) {
                        $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                            ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                            ->orWhere(function ($q) {
                                $q->where('start_time', '<=', $this->start_time)
                                  ->where('end_time', '>=', $this->end_time);
                            });
                    })
                    ->exists();

                if ($conflicts) {
                    $validator->errors()->add('start_time', 'The selected time slot conflicts with an existing appointment.');
                }
            }
        });
    }
}
