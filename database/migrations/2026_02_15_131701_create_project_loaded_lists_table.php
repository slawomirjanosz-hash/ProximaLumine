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
        Schema::create('project_loaded_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('project_list_id')->constrained('project_lists')->onDelete('cascade');
            $table->boolean('is_complete')->default(true); // czy wszystkie produkty zostały dodane
            $table->json('missing_items')->nullable(); // JSON z brakującymi produktami
            $table->integer('added_count')->default(0); // ile produktów dodano
            $table->integer('total_count')->default(0); // ile produktów było na liście
            $table->timestamps();
            
            // Zapobiega duplikatom tej samej listy w tym samym projekcie
            $table->unique(['project_id', 'project_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_loaded_lists');
    }
};
