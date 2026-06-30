<?php

namespace App\Services;

use App\Models\Booking;
use App\Exceptions\BookingConflictException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            $this->assertNoConflict($data['facility_id'], $data['start_time'], $data['end_time']);
            $this->assertUserBookingLimit($userId);

            return Booking::create([
                ...$data,
                'user_id' => $userId,
                'status' => 'pending',
            ]);
        });
    }

    protected function assertNoConflict(int $facilityId, string $startTime, string $endTime): void
    {
        $conflict = Booking::where('facility_id', $facilityId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists();

        if ($conflict) {
            throw new BookingConflictException();
        }
    }

    protected function assertUserBookingLimit(int $userId): void
    {
        $activeCount = Booking::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activeCount >= 3) {
            throw new BookingConflictException('Kamu sudah punya 2 booking aktif. Selesaikan atau batalkan salah satu dulu.');
        }
    }
}