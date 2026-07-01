<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomoraApiTest extends TestCase
{
    use RefreshDatabase;

    private function createFacility(): Facility
    {
        return Facility::create([
            'name' => 'Ruang Belajar',
            'description' => 'Ruang belajar untuk penghuni asrama',
            'capacity' => 20,
            'location' => 'Lantai 1',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'status' => 'available',
        ]);
    }

    private function validBookingPayload(Facility $facility): array
    {
        return [
            'facility_id' => $facility->id,
            'start_time' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'purpose' => 'Belajar kelompok',
        ];
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user',
            'token',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    public function test_user_can_create_booking(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $facility = $this->createFacility();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($facility));

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'status' => 'pending',
        ]);
    }

    public function test_double_booking_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'user',
        ]);

        $facility = $this->createFacility();

        $payload = $this->validBookingPayload($facility);

        Booking::create([
            'user_id' => $otherUser->id,
            'facility_id' => $facility->id,
            'start_time' => $payload['start_time'],
            'end_time' => $payload['end_time'],
            'purpose' => 'Booking pertama',
            'status' => 'approved',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(409);
    }

    public function test_regular_user_cannot_access_admin_approve_booking(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $facility = $this->createFacility();

        $booking = Booking::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'start_time' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'purpose' => 'Belajar kelompok',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/admin/bookings/{$booking->id}/approve");

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_booking(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $facility = $this->createFacility();

        $booking = Booking::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'start_time' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'purpose' => 'Belajar kelompok',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/bookings/{$booking->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
        ]);
    }
}