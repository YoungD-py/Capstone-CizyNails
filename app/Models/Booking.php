<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'booking_date',
        'booking_time',
        'total_duration_minutes',
        'needs_removal',
        'price',
        'status',
        'notes',
        'payment_proof_path',
        'payment_status',
        'payment_verified_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'payment_verified_at' => 'datetime',
        'needs_removal' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
