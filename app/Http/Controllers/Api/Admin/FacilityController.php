<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function index()
    {
        return Facility::all();
    }

    public function store(StoreFacilityRequest $request)
    {
        $facility = Facility::create($request->validated());

        return response()->json($facility, 201);
    }

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        $facility->update($request->validated());

        return response()->json($facility);
    }

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