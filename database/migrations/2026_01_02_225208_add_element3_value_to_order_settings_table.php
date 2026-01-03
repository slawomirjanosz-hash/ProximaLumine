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
            $table->string('element3_value')->nullable()->after('element3_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->dropColumn('element3_value');
        });
    }
};
