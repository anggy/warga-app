<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    protected $fillable = ['block', 'number', 'address', 'status', 'latitude', 'longitude'];

    public function residents()
    {
        return $this->belongsToMany(Resident::class)->withPivot('role');
    }

    public function iplPayments()
    {
        return $this->hasMany(IplPayment::class);
    }
}
