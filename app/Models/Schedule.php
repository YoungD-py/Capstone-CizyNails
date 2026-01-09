<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot',
        'nails_art_booked',
        'eyelash_booked',
        'type_bookings',
    ];

    protected $casts = [
        'type_bookings' => 'array',
    ];

    // Helper method to get bookings for a specific type
    public function getTypeBookedCount($typeId)
    {
        if (!$this->type_bookings) {
            return 0;
        }
        return $this->type_bookings[$typeId] ?? 0;
    }

    // Helper method to increment type booking
    public function incrementTypeBooking($typeId)
    {
        $typeBookings = $this->type_bookings ?? [];
        $typeBookings[$typeId] = ($typeBookings[$typeId] ?? 0) + 1;
        $this->type_bookings = $typeBookings;
        $this->save();
    }

    // Helper method to decrement type booking
    public function decrementTypeBooking($typeId)
    {
        $typeBookings = $this->type_bookings ?? [];
        if (isset($typeBookings[$typeId]) && $typeBookings[$typeId] > 0) {
            $typeBookings[$typeId]--;
            $this->type_bookings = $typeBookings;
            $this->save();
        }
    }

    // This model tracks per-date and per-time slot capacity
}

