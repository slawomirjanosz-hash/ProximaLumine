<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add assigned_user_id to gantt_tasks
        if (Schema::hasTable('gantt_tasks') && !Schema::hasColumn('gantt_tasks', 'assigned_user_id')) {
            Schema::table('gantt_tasks', function (Blueprint $table) {
                $table->foreignId('assigned_user_id')->nullable()->after('assignee')->constrained('users')->onDelete('set null');
            });
        }

        // Add gantt_task_id and project_id to crm_tasks
        if (Schema::hasTable('crm_tasks')) {
            Schema::table('crm_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_tasks', 'project_id')) {
                    $table->foreignId('project_id')->nullable()->after('company_id')->constrained('projects')->onDelete('cascade');
                }
                if (!Schema::hasColumn('crm_tasks', 'gantt_task_id')) {
                    $table->unsignedBigInteger('gantt_task_id')->nullable()->after('project_id');
                    $table->foreign('gantt_task_id')->references('id')->on('gantt_tasks')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_tasks')) {
            Schema::table('crm_tasks', function (Blueprint $table) {
                if (Schema::hasColumn('crm_tasks', 'gantt_task_id')) {
                    $table->dropForeign(['gantt_task_id']);
                    $table->dropColumn('gantt_task_id');
                }
                if (Schema::hasColumn('crm_tasks', 'project_id')) {
                    $table->dropForeign(['project_id']);
                    $table->dropColumn('project_id');
                }
            });
        }

        if (Schema::hasTable('gantt_tasks') && Schema::hasColumn('gantt_tasks', 'assigned_user_id')) {
            Schema::table('gantt_tasks', function (Blueprint $table) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            });
        }
    }
};
