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
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->integer('order'); // kolejność kroku
            $table->enum('type', ['action', 'ingredient']); // typ kroku: czynność lub składnik
            
            // Dla czynności (action)
            $table->string('action_name')->nullable(); // np. "Mieszanie", "Podgrzewanie"
            $table->text('action_description')->nullable();
            $table->integer('duration')->nullable(); // czas w sekundach (dla wyczekiwania)
            
            // Dla składników (ingredient)
            $table->foreignId('ingredient_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 10, 2)->nullable(); // ilość składnika
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_steps');
    }
};
