<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'subtype_id',
        'description',
        'image_path',
        'duration_minutes',
        'price',
        'is_active',
        'enable_removal',
    ];

    protected $casts = [
        'enable_removal' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function subtype()
    {
        return $this->belongsTo(Subtype::class);
    }
}
