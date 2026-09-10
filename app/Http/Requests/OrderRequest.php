<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id,active,1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.presentation_id' => [
                'required',
                'distinct',
                'exists:presentations,id,active,1',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Agrega al menos un producto al pedido.',
            'items.min' => 'Agrega al menos un producto al pedido.',
            'items.*.presentation_id.distinct' => 'No puedes repetir la misma presentación.',
            'items.*.quantity.min' => 'Las cantidades deben ser mayores a cero.',
        ];
    }
}