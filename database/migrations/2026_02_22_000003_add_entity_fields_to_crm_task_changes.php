<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('crm_task_changes', function (Blueprint $table) {
            $table->string('entity_type')->nullable()->after('task_id'); // 'task', 'deal', 'company', 'activity'
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
        });
    }

    public function down()
    {
        Schema::table('crm_task_changes', function (Blueprint $table) {
            $table->dropColumn(['entity_type', 'entity_id']);
        });
    }
};
