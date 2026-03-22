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
        Schema::table('project_finance', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('name');
            $table->string('document_number')->nullable()->after('supplier');
            $table->text('description')->nullable()->after('document_number');
            $table->date('payment_date')->nullable()->after('date');
            $table->integer('import_row_order')->nullable()->after('order');

            $table->index(['project_id', 'category', 'import_row_order'], 'project_finance_import_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_finance', function (Blueprint $table) {
            $table->dropIndex('project_finance_import_order_idx');
            $table->dropColumn(['supplier', 'document_number', 'description', 'payment_date', 'import_row_order']);
        });
    }
};
