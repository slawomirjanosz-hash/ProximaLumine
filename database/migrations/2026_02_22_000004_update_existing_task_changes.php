<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Zaktualizuj istniejące wpisy, które mają task_id
        DB::table('crm_task_changes')
            ->whereNotNull('task_id')
            ->whereNull('entity_type')
            ->update([
                'entity_type' => 'task',
                'entity_id' => DB::raw('task_id')
            ]);
    }

    public function down()
    {
        // Nie ma potrzeby cofania tej migracji
    }
};
