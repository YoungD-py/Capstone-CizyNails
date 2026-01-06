<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'staff_count',
    ];

    public function subtypes()
    {
        return $this->hasMany(Subtype::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
