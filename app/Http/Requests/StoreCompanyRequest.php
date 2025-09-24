<?php

// app/Http/Requests/StoreCompanyRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Will be handled by middleware/policies
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:companies,slug', 'regex:/^[a-z0-9-]+$/'],
            'timezone' => ['nullable', 'string', 'max:50', 'timezone'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and dashes.',
            'timezone.timezone' => 'The timezone must be a valid timezone.',
            'currency.size' => 'The currency must be exactly 3 characters.',
        ];
    }
}