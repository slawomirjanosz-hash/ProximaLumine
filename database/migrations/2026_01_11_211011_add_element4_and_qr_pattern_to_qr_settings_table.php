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
            $table->string('separator3', 5)->default('_')->after('element3_value');
            $table->string('element4_type', 20)->default('empty')->after('separator3');
            $table->integer('start_number')->default(1)->after('element4_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_settings', function (Blueprint $table) {
            $table->dropColumn(['separator3', 'element4_type', 'start_number']);
        });
    }
};
