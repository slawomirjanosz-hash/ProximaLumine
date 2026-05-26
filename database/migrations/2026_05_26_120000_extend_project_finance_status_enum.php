<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `project_finance` MODIFY `status` ENUM(
                'paid', 'ordered', 'planned', 'received', 'pending',
                'not_sent', 'sent', 'in_progress', 'completed'
            ) NOT NULL DEFAULT 'planned'");
        }
        // PostgreSQL używa string/check constraint — nie wymaga zmiany (przyjmuje dowolny string)
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `project_finance` MODIFY `status` ENUM(
                'paid', 'ordered', 'planned', 'received', 'pending'
            ) NOT NULL DEFAULT 'planned'");
        }
    }
};
