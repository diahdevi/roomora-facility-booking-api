<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FacilityController extends Controller
{
    // Lihat semua fasilitas yang aktif
    public function index()
    {
        return Facility::where('status', 'available')->get();
    }

    // Lihat detail satu fasilitas
    public function show(Facility $facility)
    {
        if ($facility->status === 'inactive') {
            return response()->json([
                'message' => 'Fasilitas tidak tersedia.',
            ], 404);
        }

        return response()->json($facility);
    }

    public function availability(Request $request, Facility $facility)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::parse($request->date);

        // Ambil semua booking aktif (pending/approved) di fasilitas & tanggal ini
        $bookedSlots = $facility->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time', 'status'])
            ->map(function ($booking) {
                return [
                    'start_time' => $booking->start_time->format('H:i'),
                    'end_time' => $booking->end_time->format('H:i'),
                    'status' => $booking->status,
                ];
            });

        return response()->json([
            'facility' => $facility->only(['id', 'name', 'opening_time', 'closing_time']),
            'date' => $date->toDateString(),
            'booked_slots' => $bookedSlots,
        ]);
    }
}