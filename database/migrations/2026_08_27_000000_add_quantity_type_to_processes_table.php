<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->change();
            $table->string('quantity_type')->default('pieces')->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropColumn('quantity_type');
            $table->integer('quantity')->change();
        });
    }
};