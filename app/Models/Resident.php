<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = [
        'full_name',
        'nik',
        'phone',
        'status',
        'family_card_number',
        'is_head_of_family',
        'family_relation',
        'place_of_birth',
        'date_of_birth',
        'occupation',
        'marital_status',
        'religion',
        'kk_file',
        'ktp_file',
    ];

    public function houses()
    {
        return $this->belongsToMany(House::class)->withPivot('role');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
