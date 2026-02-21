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
        Schema::create('project_finance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->enum('type', ['income', 'expense'])->comment('Typ transakcji: przychód lub wydatek');
            $table->string('category')->nullable()->comment('Kategoria wydatku: materials, services (tylko dla type=expense)');
            $table->string('name')->comment('Nazwa transzy, płatności lub wydatku');
            $table->decimal('amount', 12, 2)->comment('Kwota w PLN');
            $table->date('date')->comment('Data planowana lub faktyczna');
            $table->enum('status', ['paid', 'ordered', 'planned', 'received', 'pending'])->default('planned')->comment('Status: paid/ordered/planned dla wydatków, received/pending dla przychodów');
            $table->integer('order')->default(0)->comment('Kolejność sortowania');
            $table->timestamps();
            
            // Indeksy dla optymalizacji zapytań
            $table->index('project_id');
            $table->index('type');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_finance');
    }
};
