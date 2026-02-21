<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sprawdź czy kolumna już istnieje
        if (!Schema::hasColumn('crm_stages', 'is_closed')) {
            Schema::table('crm_stages', function (Blueprint $table) {
                $table->boolean('is_closed')->default(false)->after('is_active');
            });
            
            // Ustaw is_closed = 1 dla domyślnych etapów zamykających
            DB::table('crm_stages')
                ->whereIn('slug', ['wygrana', 'przegrana'])
                ->update(['is_closed' => 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('crm_stages', 'is_closed')) {
            Schema::table('crm_stages', function (Blueprint $table) {
                $table->dropColumn('is_closed');
            });
        }
    }
};
