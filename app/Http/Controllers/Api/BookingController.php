<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    #[OA\Post(
        path: '/api/bookings',
        summary: 'Buat booking baru',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['facility_id', 'start_time', 'end_time', 'purpose'],
                properties: [
                    new OA\Property(property: 'facility_id', type: 'integer', example: 1),
                    new OA\Property(property: 'start_time', type: 'string', example: '2026-07-05 10:00:00'),
                    new OA\Property(property: 'end_time', type: 'string', example: '2026-07-05 12:00:00'),
                    new OA\Property(property: 'purpose', type: 'string', example: 'Belajar kelompok untuk tugas akhir'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Booking berhasil dibuat'),
            new OA\Response(response: 409, description: 'Slot waktu sudah dibooking'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]


    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookingService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json($booking, 201);
    }

    #[OA\Get(
        path: '/api/my-bookings',
        summary: 'Lihat riwayat booking milik sendiri',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Riwayat booking berhasil diambil'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]

    public function myBookings(Request $request)
    {
        return $request->user()->bookings()->with('facility')->latest()->get();
    }

    #[OA\Patch(
        path: '/api/bookings/{id}/cancel',
        summary: 'Batalkan booking milik sendiri',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking berhasil dibatalkan'),
            new OA\Response(response: 403, description: 'Bukan booking milik kamu'),
            new OA\Response(response: 422, description: 'Booking tidak bisa dibatalkan'),
        ]
    )]
    
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