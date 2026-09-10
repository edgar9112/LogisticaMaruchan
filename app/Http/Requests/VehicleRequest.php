<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:100',
                Rule::unique('vehicles', 'code')->ignore($this->route('vehicle')),
            ],
            'plate' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'El código del vehículo ya está registrado.',
            'code.required' => 'El código del vehículo es obligatorio.',
        ];
    }
}
