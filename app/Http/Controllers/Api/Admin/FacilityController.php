<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;
use OpenApi\Attributes as OA;

class FacilityController extends Controller
{
    #[OA\Get(
        path: '/api/admin/facilities',
        summary: 'Lihat semua fasilitas (admin)',
        tags: ['Admin - Facilities'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Daftar fasilitas'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]

    public function index()
    {
        return Facility::all();
    }

    #[OA\Post(
        path: '/api/admin/facilities',
        summary: 'Tambah fasilitas baru',
        tags: ['Admin - Facilities'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'status', 'opening_time', 'closing_time'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Aula Kecil'),
                    new OA\Property(property: 'description', type: 'string', example: 'Aula untuk acara kecil'),
                    new OA\Property(property: 'location', type: 'string', example: 'Lantai 2'),
                    new OA\Property(property: 'capacity', type: 'integer', example: 30),
                    new OA\Property(property: 'status', type: 'string', enum: ['available', 'maintenance', 'inactive'], example: 'available'),
                    new OA\Property(property: 'opening_time', type: 'string', example: '08:00'),
                    new OA\Property(property: 'closing_time', type: 'string', example: '21:00'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Fasilitas berhasil ditambahkan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]

    public function store(StoreFacilityRequest $request)
    {
        $facility = Facility::create($request->validated());

        return response()->json($facility, 201);
    }

    #[OA\Put(
        path: '/api/admin/facilities/{id}',
        summary: 'Update fasilitas',
        tags: ['Admin - Facilities'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fasilitas berhasil diupdate'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        $facility->update($request->validated());

        return response()->json($facility);
    }

    #[OA\Delete(
        path: '/api/admin/facilities/{id}',
        summary: 'Hapus fasilitas',
        tags: ['Admin - Facilities'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fasilitas berhasil dihapus'),
            new OA\Response(response: 422, description: 'Masih ada booking aktif'),
        ]
    )]

    public function destroy(Facility $facility)
    {
        // Cegah hapus facility yang masih punya booking aktif
        $hasActiveBooking = $facility->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasActiveBooking) {
            return response()->json([
                'message' => 'Tidak bisa menghapus fasilitas yang masih punya booking aktif.',
            ], 422);
        }

        $facility->delete();

        return response()->json(['message' => 'Fasilitas berhasil dihapus.']);
    }
}