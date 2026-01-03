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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_view_catalog')->default(false)->after('password');
            $table->boolean('can_add')->default(false)->after('can_view_catalog');
            $table->boolean('can_remove')->default(false)->after('can_add');
            $table->boolean('can_orders')->default(false)->after('can_remove');
            $table->boolean('can_settings')->default(false)->after('can_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_view_catalog', 'can_add', 'can_remove', 'can_orders', 'can_settings']);
        });
    }
};
