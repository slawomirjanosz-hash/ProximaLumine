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
        Schema::table('project_removals', function (Blueprint $table) {
            // Sprawdź czy kolumna już istnieje (jak w RJ)
            if (!Schema::hasColumn('project_removals', 'authorized')) {
                $table->boolean('authorized')->default(true)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_removals', function (Blueprint $table) {
            if (Schema::hasColumn('project_removals', 'authorized')) {
                $table->dropColumn('authorized');
            }
        });
    }
};
