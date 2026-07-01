<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class FacilityController extends Controller
{

    #[OA\Get(
        path: '/api/facilities',
        summary: 'Lihat daftar fasilitas yang tersedia',
        tags: ['Facilities'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Daftar fasilitas berhasil diambil'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]

    // Lihat semua fasilitas yang aktif
    public function index()
    {
        return Facility::where('status', 'available')->get();
    }

    #[OA\Get(
        path: '/api/facilities/{id}',
        summary: 'Lihat detail fasilitas',
        tags: ['Facilities'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail fasilitas'),
            new OA\Response(response: 404, description: 'Fasilitas tidak ditemukan atau inactive'),
        ]
    )]

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

    #[OA\Get(
        path: '/api/facilities/{id}/availability',
        summary: 'Cek jadwal kosong fasilitas pada tanggal tertentu',
        tags: ['Facilities'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date'), example: '2026-07-05'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data availability berhasil diambil'),
            new OA\Response(response: 422, description: 'Validasi tanggal gagal'),
        ]
    )]

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