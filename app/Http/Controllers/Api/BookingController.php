<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookingService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json($booking, 201);
    }

    public function myBookings(Request $request)
    {
        return $request->user()->bookings()->with('facility')->latest()->get();
    }

    public function cancel(Request $request, Booking $booking)
    {
        // Pastikan user cuma bisa cancel booking miliknya sendiri
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Kamu tidak punya akses untuk membatalkan booking ini.',
            ], 403);
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'message' => 'Booking ini tidak bisa dibatalkan.',
            ], 422);
        }

        if ($booking->status === 'approved' && now()->addHours(2)->isAfter($booking->start_time)) {
            return response()->json([
                'message' => 'Booking yang sudah disetujui hanya bisa dibatalkan minimal 2 jam sebelum jadwal.',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json($booking);
    }
}