<?php

use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "Role: " . $role->name . "\n";
    echo "Permissions: " . implode(', ', $role->permissions->pluck('name')->toArray()) . "\n";
    echo "-------------------\n";
}
