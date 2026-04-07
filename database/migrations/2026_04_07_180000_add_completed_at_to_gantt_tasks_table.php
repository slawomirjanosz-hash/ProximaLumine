<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gantt_tasks', function (Blueprint $table) {
            $table->date('completed_at')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('gantt_tasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
