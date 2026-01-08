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
        Schema::table('residents', function (Blueprint $table) {
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('occupation')->nullable(); // Pekerjaan
            $table->string('marital_status')->nullable(); // Status Perkawinan
            $table->string('religion')->nullable(); // Agama
            $table->string('kk_file')->nullable(); // Upload KK
            $table->string('ktp_file')->nullable(); // Upload KTP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            //
        });
    }
};
