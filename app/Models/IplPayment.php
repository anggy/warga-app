<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IplPayment extends Model
{
    protected $fillable = [
        'house_id',
        'payer_name',
        'amount',
        'period',
        'paid_at',
        'status',
        'proof_of_transfer',
    ];

    public function allocations()
    {
        return $this->hasMany(IplPaymentAllocation::class);
    }

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
