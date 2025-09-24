<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'slug' => [
                'sometimes', 
                'string', 
                'max:150', 
                Rule::unique('companies', 'slug')->ignore($this->company),
                'regex:/^[a-z0-9-]+$/'
            ],
            'timezone' => ['sometimes', 'string', 'max:50', 'timezone'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}