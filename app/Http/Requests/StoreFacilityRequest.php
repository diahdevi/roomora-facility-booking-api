<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:available,maintenance,inactive'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
        ];
    }
}