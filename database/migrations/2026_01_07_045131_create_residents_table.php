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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('nik')->unique();
            $table->string('phone')->nullable();
            $table->string('status'); // permanent, contract
            $table->string('family_card_number');
            $table->boolean('is_head_of_family')->default(false);
            $table->string('family_relation'); // head, wife, child, other
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
