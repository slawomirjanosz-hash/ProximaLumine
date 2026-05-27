<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('offer_templates')) {
            return;
        }

        Schema::table('offer_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('offer_templates', 'kind')) {
                $table->string('kind', 30)->default('description')->after('name');
                $table->index('kind');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('offer_templates')) {
            return;
        }

        Schema::table('offer_templates', function (Blueprint $table) {
            if (Schema::hasColumn('offer_templates', 'kind')) {
                $table->dropIndex(['kind']);
                $table->dropColumn('kind');
            }
        });
    }
};
