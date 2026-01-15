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
        Schema::table('ipl_payments', function (Blueprint $table) {
            $table->string('proof_of_transfer')->nullable()->after('status');
            $table->decimal('allocation_security', 10, 2)->default(0)->after('proof_of_transfer');
            $table->decimal('allocation_maintenance', 10, 2)->default(0)->after('allocation_security');
            $table->decimal('allocation_resident_cash', 10, 2)->default(0)->after('allocation_maintenance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipl_payments', function (Blueprint $table) {
            $table->dropColumn([
                'proof_of_transfer',
                'allocation_security',
                'allocation_maintenance',
                'allocation_resident_cash'
            ]);
        });
    }
};
