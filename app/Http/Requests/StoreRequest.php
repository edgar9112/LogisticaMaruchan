<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stores', 'code')->ignore($this->route('store')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:100'],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'El código ya está registrado.',
        ];
    }
}