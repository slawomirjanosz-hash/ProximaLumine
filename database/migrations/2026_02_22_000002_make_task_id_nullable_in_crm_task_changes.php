<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('crm_task_changes', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('crm_task_changes', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false)->change();
        });
    }
};
