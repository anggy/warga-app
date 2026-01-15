<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IplPaymentAllocation extends Model
{
    protected $fillable = [
        'ipl_payment_id',
        'fund_id',
        'fund_name',
        'amount',
    ];

    public function iplPayment()
    {
        return $this->belongsTo(IplPayment::class);
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }
}
