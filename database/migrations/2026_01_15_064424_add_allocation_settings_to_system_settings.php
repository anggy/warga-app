<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->decimal('ipl_amount', 12, 2)->default(130000);
            $table->decimal('allocation_security_amount', 12, 2)->default(70000);
            $table->decimal('allocation_maintenance_amount', 12, 2)->default(30000);
            $table->decimal('allocation_resident_cash_amount', 12, 2)->default(30000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ipl_amount',
                'allocation_security_amount',
                'allocation_maintenance_amount',
                'allocation_resident_cash_amount'
            ]);
        });
    }
};
