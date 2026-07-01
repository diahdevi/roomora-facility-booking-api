<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingApprovalController extends Controller
{

    #[OA\Get(
        path: '/api/admin/bookings',
        summary: 'Lihat semua booking (admin)',
        tags: ['Admin - Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'cancelled', 'completed']), example: 'pending'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar booking berhasil diambil'),
            new OA\Response(response: 403, description: 'Akses ditolak, bukan admin'),
        ]
    )]

    // Lihat semua booking (untuk admin)
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'facility'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->get();
    }

    #[OA\Patch(
        path: '/api/admin/bookings/{id}/approve',
        summary: 'Approve booking',
        tags: ['Admin - Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking disetujui'),
            new OA\Response(response: 422, description: 'Booking bukan status pending'),
        ]
    )]

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

    #[OA\Patch(
        path: '/api/admin/bookings/{id}/reject',
        summary: 'Reject booking dengan alasan',
        tags: ['Admin - Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['admin_note'],
                properties: [
                    new OA\Property(property: 'admin_note', type: 'string', example: 'Ruangan sedang digunakan untuk acara lain'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Booking ditolak'),
            new OA\Response(response: 422, description: 'Validasi gagal atau bukan status pending'),
        ]
    )]

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