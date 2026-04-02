<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('schedule_enabled')->default(false)->after('profit_amount');
            $table->json('schedule')->nullable()->after('schedule_enabled');
            $table->json('payment_terms')->nullable()->after('schedule');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['schedule_enabled', 'schedule', 'payment_terms']);
        });
    }
};
