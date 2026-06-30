<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingApprovalController extends Controller
{
    // Lihat semua booking (untuk admin)
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'facility'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->get();
    }

    public function approve(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya booking dengan status pending yang bisa di-approve.',
            ], 422);
        }

        $booking->update(['status' => 'approved']);

        return response()->json($booking);
    }

    public function reject(RejectBookingRequest $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya booking dengan status pending yang bisa di-reject.',
            ], 422);
        }

        $booking->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return response()->json($booking);
    }
}