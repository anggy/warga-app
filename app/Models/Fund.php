<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_amount',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_amount' => 'decimal:2',
    ];

    public function allocations()
    {
        return $this->hasMany(IplPaymentAllocation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
