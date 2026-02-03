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
        Schema::create('catalog_columns_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_product')->default(true);
            $table->boolean('show_description')->default(true);
            $table->boolean('show_supplier')->default(true);
            $table->boolean('show_price')->default(true);
            $table->boolean('show_category')->default(true);
            $table->boolean('show_quantity')->default(true);
            $table->boolean('show_minimum')->default(true);
            $table->boolean('show_location')->default(true);
            $table->boolean('show_user')->default(true);
            $table->boolean('show_actions')->default(true);
            $table->boolean('show_qr_code')->default(false);
            $table->boolean('show_qr_description')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_columns_settings');
    }
};
