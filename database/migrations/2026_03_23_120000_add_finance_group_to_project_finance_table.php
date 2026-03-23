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
        Schema::table('project_finance', function (Blueprint $table) {
            $table->string('finance_group')->nullable()->after('description');
            $table->index(['project_id', 'category', 'finance_group'], 'project_finance_group_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_finance', function (Blueprint $table) {
            $table->dropIndex('project_finance_group_idx');
            $table->dropColumn('finance_group');
        });
    }
};
