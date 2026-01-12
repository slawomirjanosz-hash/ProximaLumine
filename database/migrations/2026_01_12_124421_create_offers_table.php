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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_number');
            $table->string('offer_title');
            $table->date('offer_date');
            $table->json('services')->nullable();
            $table->json('works')->nullable();
            $table->json('materials')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['portfolio', 'inprogress', 'archived'])->default('portfolio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
