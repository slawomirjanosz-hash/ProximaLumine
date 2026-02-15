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
            $table->boolean('can_projects_add')->default(false)->after('can_view_projects');
            $table->boolean('can_projects_in_progress')->default(false)->after('can_projects_add');
            $table->boolean('can_projects_warranty')->default(false)->after('can_projects_in_progress');
            $table->boolean('can_projects_archived')->default(false)->after('can_projects_warranty');
            $table->boolean('can_projects_settings')->default(false)->after('can_projects_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'can_projects_add',
                'can_projects_in_progress',
                'can_projects_warranty',
                'can_projects_archived',
                'can_projects_settings'
            ]);
        });
    }
};
