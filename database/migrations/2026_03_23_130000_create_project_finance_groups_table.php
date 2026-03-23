<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_finance_groups')) {
            return;
        }

        Schema::create('project_finance_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['project_id', 'name'], 'project_finance_groups_project_name_unique');
            $table->index(['project_id', 'name'], 'project_finance_groups_project_name_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('project_finance_groups')) {
            return;
        }

        Schema::dropIfExists('project_finance_groups');
    }
};
