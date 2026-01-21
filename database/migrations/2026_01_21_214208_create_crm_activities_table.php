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
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['telefon', 'email', 'spotkanie', 'notatka', 'sms', 'oferta', 'umowa', 'faktura', 'reklamacja']);
            $table->string('subject');
            $table->text('description')->nullable();
            $table->dateTime('activity_date');
            $table->integer('duration')->nullable(); // czas trwania w minutach
            $table->enum('outcome', ['pozytywny', 'neutralny', 'negatywny', 'brak_odpowiedzi'])->nullable();
            $table->foreignId('company_id')->nullable()->constrained('crm_companies')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('crm_deals')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('attachments')->nullable(); // JSON z plikami
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
