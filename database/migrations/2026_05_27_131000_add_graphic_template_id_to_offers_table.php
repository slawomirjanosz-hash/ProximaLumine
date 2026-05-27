<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('offers')) {
            return;
        }

        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'graphic_template_id')) {
                $table->unsignedBigInteger('graphic_template_id')->nullable()->after('offer_description');
                $table->index('graphic_template_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('offers')) {
            return;
        }

        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'graphic_template_id')) {
                $table->dropIndex(['graphic_template_id']);
                $table->dropColumn('graphic_template_id');
            }
        });
    }
};
