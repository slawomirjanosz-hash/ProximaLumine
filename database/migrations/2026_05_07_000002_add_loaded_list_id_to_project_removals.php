<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_removals', function (Blueprint $table) {
            // Dodaj kolumnę loaded_list_id – z której załadowanej listy pochodzi ten wpis
            // nullable – produkty dodane ręcznie (poza listami) mają NULL
            $table->unsignedBigInteger('loaded_list_id')->nullable()->after('authorized');
        });
    }

    public function down(): void
    {
        Schema::table('project_removals', function (Blueprint $table) {
            $table->dropColumn('loaded_list_id');
        });
    }
};
