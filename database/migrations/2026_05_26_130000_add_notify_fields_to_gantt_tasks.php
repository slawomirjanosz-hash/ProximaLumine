<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gantt_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('gantt_tasks', 'notify_email')) {
                $table->boolean('notify_email')->default(false)->after('assigned_user_id');
            }
            if (!Schema::hasColumn('gantt_tasks', 'notify_frequency')) {
                $table->string('notify_frequency', 200)->nullable()->after('notify_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gantt_tasks', function (Blueprint $table) {
            $table->dropColumn(['notify_email', 'notify_frequency']);
        });
    }
};
