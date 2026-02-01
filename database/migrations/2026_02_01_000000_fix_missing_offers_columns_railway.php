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
        Schema::table('offers', function (Blueprint $table) {
            // Check and add crm_deal_id if not exists
            if (!Schema::hasColumn('offers', 'crm_deal_id')) {
                $table->unsignedBigInteger('crm_deal_id')->nullable()->after('id');
                $table->foreign('crm_deal_id')->references('id')->on('crm_deals')->onDelete('set null');
            }
            
            // Check and add customer fields if not exist
            if (!Schema::hasColumn('offers', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('offer_title');
            }
            if (!Schema::hasColumn('offers', 'customer_nip')) {
                $table->string('customer_nip')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('offers', 'customer_address')) {
                $table->string('customer_address')->nullable()->after('customer_nip');
            }
            if (!Schema::hasColumn('offers', 'customer_city')) {
                $table->string('customer_city')->nullable()->after('customer_address');
            }
            if (!Schema::hasColumn('offers', 'customer_postal_code')) {
                $table->string('customer_postal_code')->nullable()->after('customer_city');
            }
            if (!Schema::hasColumn('offers', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_postal_code');
            }
            if (!Schema::hasColumn('offers', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'crm_deal_id')) {
                $table->dropForeign(['crm_deal_id']);
                $table->dropColumn('crm_deal_id');
            }
            
            $columns = ['customer_name', 'customer_nip', 'customer_address', 'customer_city', 
                       'customer_postal_code', 'customer_phone', 'customer_email'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('offers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
