<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_order_items')) {
            return;
        }
        Schema::create('project_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_finance_id')->constrained('project_finance')->onDelete('cascade');
            $table->string('name', 500)->comment('Nazwa / opis pozycji');
            $table->decimal('quantity', 10, 3)->default(1)->comment('Zamówiona ilość');
            $table->string('unit', 50)->nullable()->comment('Jednostka: szt., m, kg itp.');
            $table->decimal('amount_net', 12, 2)->nullable()->comment('Kwota netto pozycji');
            $table->decimal('received_qty', 10, 3)->default(0)->comment('Odebrana ilość');
            $table->timestamp('received_at')->nullable()->comment('Data ostatniego odbioru');
            $table->string('notes', 500)->nullable()->comment('Uwagi');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('project_finance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_order_items');
    }
};
