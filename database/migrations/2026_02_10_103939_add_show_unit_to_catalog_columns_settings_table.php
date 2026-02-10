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
        Schema::table('catalog_columns_settings', function (Blueprint $table) {
            $table->boolean('show_unit')->default(true)->after('show_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_columns_settings', function (Blueprint $table) {
            $table->dropColumn('show_unit');
        });
    }
};
