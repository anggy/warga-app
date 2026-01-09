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
    ];
}
