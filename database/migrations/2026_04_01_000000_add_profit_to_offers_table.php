<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('profit_percent', 8, 2)->default(0)->after('total_price');
            $table->decimal('profit_amount', 10, 2)->default(0)->after('profit_percent');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['profit_percent', 'profit_amount']);
        });
    }
};
