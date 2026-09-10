<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
                Rule::unique('warehouses', 'code')->ignore($this->route('warehouse')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
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