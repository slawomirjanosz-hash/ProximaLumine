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
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['telefon', 'email', 'spotkanie', 'zadanie', 'follow_up'])->default('zadanie');
            $table->enum('priority', ['niska', 'normalna', 'wysoka', 'pilna'])->default('normalna');
            $table->enum('status', ['do_zrobienia', 'w_trakcie', 'zakonczone', 'anulowane'])->default('do_zrobienia');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('crm_companies')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('crm_deals')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_tasks');
    }
};
