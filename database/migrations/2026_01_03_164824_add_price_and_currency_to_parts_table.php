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
        Schema::table('parts', function (Blueprint $table) {
            $table->decimal('net_price', 10, 2)->nullable()->after('description');
            $table->enum('currency', ['PLN', 'EUR', '$'])->default('PLN')->after('net_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn(['net_price', 'currency']);
        });
    }
};
