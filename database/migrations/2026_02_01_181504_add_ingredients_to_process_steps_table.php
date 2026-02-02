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
            // Sprawdź czy kolumna już istnieje (zabezpieczenie przed duplikatami)
            if (!Schema::hasColumn('process_steps', 'ingredients_data')) {
                $table->json('ingredients_data')->nullable(); // Dane składników: id, nazwa, ilość dodana, jednostka
            }
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
