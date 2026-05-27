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
        if (Schema::hasTable('offer_templates')) {
            return;
        }

        Schema::create('offer_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->longText('content_html')->nullable();
            $table->longText('content_json')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_templates');
    }
};
