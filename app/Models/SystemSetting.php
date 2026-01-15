<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'app_name',
        'map_latitude',
        'map_longitude',
        'map_zoom',
        'description',
        'bot_port',
        'bot_session_id',
        'ipl_amount',
        'allocation_security_amount',
        'allocation_maintenance_amount',
        'allocation_resident_cash_amount',
    ];
}
