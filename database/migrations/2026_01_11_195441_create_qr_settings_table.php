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
        Schema::create('qr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('element1_type', 20)->default('empty');
            $table->string('element1_value', 50)->nullable();
            $table->string('separator1', 5)->default('_');
            $table->string('element2_type', 20)->default('empty');
            $table->string('element2_value', 50)->nullable();
            $table->string('separator2', 5)->default('_');
            $table->string('element3_type', 20)->default('empty');
            $table->string('element3_value', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_settings');
    }
};
