<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subtype',
        'description',
        'duration_minutes',
        'staff_count',
        'price',
        'is_active',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
