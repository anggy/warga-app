<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'resident_id',
        'item_name',
        'quantity',
        'description',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
