<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'capacity',
        'status',
        'opening_time',
        'closing_time',
    ];

    // protected function casts():array
    // {
    //     return [
    //         'opening_time' => 'datetime:H:i',
    //         'closing_time' => 'datetime:H:i',
    //     ];
    // }

    // Hapus casts() yang lama, ganti dengan accessor di bawah

    protected function openingTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : null,
        );
    }

    protected function closingTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : null,
        );
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
