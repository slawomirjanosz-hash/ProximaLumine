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
        Schema::table('gantt_tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('dependencies');
        });
    }

    public function down(): void
    {
        Schema::table('gantt_tasks', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
