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
            $table->boolean('export_all_products')->default(true)->after('show_qr_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_columns_settings', function (Blueprint $table) {
            $table->dropColumn('export_all_products');
        });
    }
};
