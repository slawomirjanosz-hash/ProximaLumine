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
            $table->string('separator1', 5)->nullable()->after('element1_value');
            $table->string('separator2', 5)->nullable()->after('element2_value');
            $table->string('separator3', 5)->nullable()->after('element3_digits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->dropColumn(['separator1', 'separator2', 'separator3']);
        });
    }
};
