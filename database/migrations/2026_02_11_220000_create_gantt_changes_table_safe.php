<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ✅ ZAWSZE sprawdzaj czy tabela już istnieje
        if (!Schema::hasTable('gantt_changes')) {
            Schema::create('gantt_changes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action'); // 'add', 'edit', 'delete', 'move'
                $table->string('task_name');
                $table->text('details')->nullable();
                $table->timestamp('created_at')->useCurrent();
                
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('gantt_changes');
    }
};
