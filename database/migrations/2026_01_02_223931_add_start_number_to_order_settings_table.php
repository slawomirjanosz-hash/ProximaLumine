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
            $table->integer('start_number')->nullable()->after('element3_digits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->dropColumn('start_number');
        });
    }
};
