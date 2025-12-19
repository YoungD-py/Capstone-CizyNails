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
    ];

    // This model tracks per-date and per-time slot capacity, not per-service relations
}
