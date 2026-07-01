<h1 align="center">Roomora Facility Booking API</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat" alt="License">
</p>

Roomora adalah REST API untuk sistem booking fasilitas asrama berbasis web. Dibangun menggunakan Laravel 11 dengan autentikasi berbasis token (Laravel Sanctum), fitur anti double booking menggunakan database-level locking, dan dokumentasi API lengkap menggunakan Swagger/OpenAPI 3.0.

---

## Fitur Utama

- **Autentikasi** — Register, login, logout menggunakan Laravel Sanctum token
- **Manajemen Fasilitas** — CRUD fasilitas asrama oleh admin
- **Booking Fasilitas** — User bisa cek jadwal kosong dan buat booking
- **Anti Double Booking** — Mencegah dua user booking fasilitas yang sama di waktu yang sama menggunakan `DB::transaction()` + `lockForUpdate()`
- **Approval Flow** — Admin approve/reject booking dengan catatan alasan
- **Cancel Booking** — User bisa membatalkan booking dengan aturan waktu
- **Availability Check** — Cek slot yang sudah terisi per fasilitas per tanggal
- **Dokumentasi API** — Swagger UI tersedia di `/api/documentation`

---

## Tech Stack

| Layer       | Teknologi                |
| ----------- | ------------------------ |
| Backend     | Laravel 11               |
| Auth        | Laravel Sanctum          |
| Database    | MySQL / SQLite           |
| API Docs    | L5-Swagger (OpenAPI 3.0) |
| API Testing | Postman                  |

---

## Struktur Database

```
users           — data penghuni dan admin asrama
facilities      — data fasilitas yang bisa dibooking
bookings        — data booking dengan status tracking
```

**Relasi:**

- `users` 1 → many `bookings`
- `facilities` 1 → many `bookings`

---

## API Endpoints

### Auth

| Method | Endpoint          | Deskripsi           | Auth |
| ------ | ----------------- | ------------------- | ---- |
| POST   | `/api/register` | Register user baru  | -    |
| POST   | `/api/login`    | Login, return token | -    |
| POST   | `/api/logout`   | Logout              | Yes  |

### Facilities (User)

| Method | Endpoint                                    | Deskripsi               | Auth |
| ------ | ------------------------------------------- | ----------------------- | ---- |
| GET    | `/api/facilities`                         | List fasilitas tersedia | Yes  |
| GET    | `/api/facilities/{id}`                    | Detail fasilitas        | Yes  |
| GET    | `/api/facilities/{id}/availability?date=` | Cek jadwal kosong       | Yes  |

### Bookings (User)

| Method | Endpoint                      | Deskripsi               | Auth |
| ------ | ----------------------------- | ----------------------- | ---- |
| POST   | `/api/bookings`             | Buat booking baru       | Yes  |
| GET    | `/api/my-bookings`          | Riwayat booking sendiri | Yes  |
| PATCH  | `/api/bookings/{id}/cancel` | Batalkan booking        | Yes  |

### Admin

| Method | Endpoint                             | Deskripsi             | Auth  |
| ------ | ------------------------------------ | --------------------- | ----- |
| GET    | `/api/admin/bookings`              | Lihat semua booking   | Admin |
| PATCH  | `/api/admin/bookings/{id}/approve` | Approve booking       | Admin |
| PATCH  | `/api/admin/bookings/{id}/reject`  | Reject booking        | Admin |
| GET    | `/api/admin/facilities`            | Lihat semua fasilitas | Admin |
| POST   | `/api/admin/facilities`            | Tambah fasilitas      | Admin |
| PUT    | `/api/admin/facilities/{id}`       | Update fasilitas      | Admin |
| DELETE | `/api/admin/facilities/{id}`       | Hapus fasilitas       | Admin |

---

## Business Rules

- Booking hanya bisa dilakukan untuk H+0 sampai H+7
- Minimal 1 jam dari waktu submit
- Durasi booking: minimal 30 menit, maksimal 3 jam
- Maksimal 2 booking aktif per user dalam waktu bersamaan
- Cancel `pending` → kapan saja sebelum `start_time`
- Cancel `approved` → minimal 2 jam sebelum `start_time`
- Admin reject **wajib** menyertakan `admin_note`

---

## Status Booking

```
pending → approved → completed
pending → rejected
approved → cancelled
pending → cancelled
```

---

## Cara Menjalankan Project

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL atau SQLite

### Instalasi

```bash
# Clone repository
git clone https://github.com/username/roomora.git
cd roomora

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Setup database di .env
DB_CONNECTION=mysql
DB_DATABASE=booking_asrama
DB_USERNAME=root
DB_PASSWORD=

# Jalankan migration
php artisan migrate

# Install Sanctum
php artisan install:api

# Buat user admin pertama
php artisan tinker
>>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin']);

# Jalankan server
php artisan serve
```

### Akses Dokumentasi API

```
http://127.0.0.1:8000/api/documentation
```

Generate ulang dokumentasi setelah ada perubahan anotasi:

```bash
php artisan l5-swagger:generate
```

---

## Highlight Teknis

### Anti Double Booking dengan Database Lock

Sistem menggunakan `DB::transaction()` + `lockForUpdate()` untuk mencegah race condition saat dua user booking slot yang sama secara bersamaan. Validasi dilakukan di database level, bukan hanya application level.

```php
return DB::transaction(function () use ($data, $userId) {
    $conflict = Booking::where('facility_id', $data['facility_id'])
        ->whereIn('status', ['pending', 'approved'])
        ->where('start_time', '<', $data['end_time'])
        ->where('end_time', '>', $data['start_time'])
        ->lockForUpdate()
        ->exists();

    if ($conflict) {
        throw new BookingConflictException();
    }

    return Booking::create([...$data, 'user_id' => $userId]);
});
```

### Role-based Access Control

Endpoint admin dilindungi middleware `EnsureUserIsAdmin` yang dicek setelah Sanctum token diverifikasi.

---

## Postman Collection

Import file koleksi Postman yang tersedia di repository untuk langsung testing semua endpoint:

📁 `roomora-api.postman_collection.json`

---

## Lisensi

MIT License — bebas digunakan untuk keperluan belajar dan portfolio.

## Testing

This project includes automated API tests using PHPUnit.

Run tests:

```bash
php artisan test
```


<p align="center">
  <img src="https://github.com/diahdevi/roomora-facility-booking-api/actions/workflows/laravel-tests.yml/badge.svg" alt="Laravel Tests">
</p>


