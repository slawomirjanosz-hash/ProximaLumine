<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_tasks')) {
            return;
        }

        Schema::table('crm_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_tasks', 'notify_email')) {
                $table->boolean('notify_email')->default(false)->after('gantt_task_id');
            }
            if (!Schema::hasColumn('crm_tasks', 'notify_frequency')) {
                $table->string('notify_frequency', 200)->nullable()->after('notify_email');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('crm_tasks')) {
            return;
        }

        Schema::table('crm_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('crm_tasks', 'notify_frequency')) {
                $table->dropColumn('notify_frequency');
            }
            if (Schema::hasColumn('crm_tasks', 'notify_email')) {
                $table->dropColumn('notify_email');
            }
        });
    }
};
