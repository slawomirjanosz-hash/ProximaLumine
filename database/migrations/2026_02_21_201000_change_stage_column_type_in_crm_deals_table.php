<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->string('stage', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jeśli wcześniej był ENUM, nie da się automatycznie przywrócić
        // Możesz ustawić np. VARCHAR(50) lub ENUM ręcznie
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->string('stage', 50)->change();
        });
    }
};
