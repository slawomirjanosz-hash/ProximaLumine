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
        Schema::table('qr_settings', function (Blueprint $table) {
            $table->string('start_number', 10)->default('1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_settings', function (Blueprint $table) {
            $table->integer('start_number')->default(1)->change();
        });
    }
};
