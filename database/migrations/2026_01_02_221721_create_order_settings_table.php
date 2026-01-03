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
        Schema::create('order_settings', function (Blueprint $table) {
            $table->id();
            $table->string('element1_type')->nullable();
            $table->string('element1_value')->nullable();
            $table->string('element2_type')->nullable();
            $table->string('element2_value')->nullable();
            $table->string('element3_type')->nullable();
            $table->integer('element3_digits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_settings');
    }
};
