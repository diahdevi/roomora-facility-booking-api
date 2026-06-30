<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_note' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_note.required' => 'Alasan penolakan wajib diisi.',
            'admin_note.min' => 'Alasan penolakan minimal 5 karakter.',
        ];
    }
}