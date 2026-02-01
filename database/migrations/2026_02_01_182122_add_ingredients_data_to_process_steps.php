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
        Schema::table('process_steps', function (Blueprint $table) {
            $table->json('ingredients_data')->nullable(); // Dane składników: [{ingredient_id, name, quantity_added, unit, quantity_remaining}]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_steps', function (Blueprint $table) {
            $table->dropColumn('ingredients_data');
        });
    }
};
