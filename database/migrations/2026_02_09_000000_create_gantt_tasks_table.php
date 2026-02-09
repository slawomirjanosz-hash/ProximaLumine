<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('gantt_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->date('start');
            $table->date('end');
            $table->integer('progress')->default(0);
            $table->string('dependencies')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('gantt_tasks');
    }
};