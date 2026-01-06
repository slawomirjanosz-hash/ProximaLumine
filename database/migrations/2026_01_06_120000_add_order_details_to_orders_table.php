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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('supplier_offer_number')->nullable()->after('products');
            $table->string('payment_method')->nullable()->after('supplier_offer_number');
            $table->string('payment_days')->nullable()->after('payment_method');
            $table->string('delivery_time')->nullable()->after('payment_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['supplier_offer_number', 'payment_method', 'payment_days', 'delivery_time']);
        });
    }
};
