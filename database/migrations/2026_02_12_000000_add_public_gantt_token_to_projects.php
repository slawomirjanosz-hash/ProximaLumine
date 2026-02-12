<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('projects', 'public_gantt_token')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('public_gantt_token', 64)->nullable()->unique()->after('loaded_list_id');
            });
        }
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('public_gantt_token');
        });
    }
};
