<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'presentation_type' => ['required', 'in:vaso,bolsa,caja'],
            'flavor' => ['nullable', 'string', 'max:255'],
            'pieces_per_box' => ['nullable', 'integer', 'min:0'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('presentations', 'sku')->ignore($this->route('presentation')),
            ],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'presentation_type.in' => 'El tipo debe ser vaso, bolsa o caja.',
            'sku.unique' => 'El SKU ya está registrado.',
        ];
    }
}