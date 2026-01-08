<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'license_plate',
        'vehicle_type',
        'brand',
        'color',
        'photo',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
