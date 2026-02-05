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
        Schema::table('qr_settings', function (Blueprint $table) {
            $table->string('generation_mode', 20)->default('auto')->after('code_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_settings', function (Blueprint $table) {
            $table->dropColumn('generation_mode');
        });
    }
};
