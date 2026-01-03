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
        Schema::create('part_removals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('part_name'); // Nazwa produktu w momencie pobrania
            $table->string('description')->nullable(); // Opis produktu
            $table->integer('quantity'); // Ilość pobrana
            $table->decimal('price', 10, 2)->nullable(); // Cena jednostkowa
            $table->string('currency', 10)->default('PLN'); // Waluta
            $table->integer('stock_after'); // Stan magazynu po pobraniu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_removals');
    }
};
