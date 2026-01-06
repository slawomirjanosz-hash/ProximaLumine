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
            // Granularne uprawnienia do ustawień
            $table->boolean('can_settings_categories')->default(true)->after('can_settings');
            $table->boolean('can_settings_suppliers')->default(true)->after('can_settings_categories');
            $table->boolean('can_settings_company')->default(true)->after('can_settings_suppliers');
            $table->boolean('can_settings_users')->default(true)->after('can_settings_company');
            $table->boolean('can_settings_export')->default(true)->after('can_settings_users');
            $table->boolean('can_settings_other')->default(true)->after('can_settings_export');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'can_settings_categories',
                'can_settings_suppliers',
                'can_settings_company',
                'can_settings_users',
                'can_settings_export',
                'can_settings_other',
            ]);
        });
    }
};
