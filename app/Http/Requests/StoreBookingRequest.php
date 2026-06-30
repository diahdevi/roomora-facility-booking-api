<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Override;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'exists:facilities,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'purpose' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    public function messages() : array {
        return [
            'start_time.after' => 'Waktu booking tidak boleh di masa lalu.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'purpose.min' => 'Tujuan penggunaan minimal 10 karakter.',
        ];
    }

    // Validasi tambahan yang gak bisa pakai rule bawaan Laravel
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);

            // Durasi maksimal 2 jam
            if ($start->diffInMinutes($end) > 120) {
                $validator->errors()->add('end_time', 'Durasi booking maksimal 3 jam.');
            }

            // Durasi minimal 30 menit
            if ($start->diffInMinutes($end) < 30) {
                $validator->errors()->add('end_time', 'Durasi booking minimal 30 menit.');
            }

            // Booking maksimal 7 hari ke depan
            if ($start->isAfter(now()->addDays(7))) {
                $validator->errors()->add('start_time', 'Booking maksimal 7 hari ke depan.');
            }

            // Jarak minimal 1 jam dari sekarang
            if ($start->isBefore(now()->addHour())) {
                $validator->errors()->add('start_time', 'Booking harus minimal 1 jam dari sekarang.');
            }
        });
    }
}
