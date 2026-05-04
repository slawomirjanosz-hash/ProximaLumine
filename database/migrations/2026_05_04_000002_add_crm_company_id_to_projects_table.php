<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'crm_company_id')) {
                $table->unsignedBigInteger('crm_company_id')->nullable()->after('source_offer_id');
                $table->foreign('crm_company_id')->references('id')->on('crm_companies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'crm_company_id')) {
                $table->dropForeign(['crm_company_id']);
                $table->dropColumn('crm_company_id');
            }
        });
    }
};
