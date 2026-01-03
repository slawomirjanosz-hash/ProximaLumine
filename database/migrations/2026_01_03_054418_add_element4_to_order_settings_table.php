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
        Schema::table('order_settings', function (Blueprint $table) {
            $table->string('element4_type')->nullable()->after('separator3');
            $table->string('element4_value')->nullable()->after('element4_type');
            $table->string('separator4', 5)->nullable()->after('element4_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->dropColumn(['element4_type', 'element4_value', 'separator4']);
        });
    }
};
