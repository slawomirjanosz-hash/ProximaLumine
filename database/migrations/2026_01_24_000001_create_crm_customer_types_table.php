<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#888888');
            $table->timestamps();
        });

        // Add default types with auto-assigned colors
        DB::table('crm_customer_types')->insert([
            ['name' => 'Klient', 'slug' => 'klient', 'color' => '#2563eb', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Potencjalny', 'slug' => 'potencjalny', 'color' => '#f59e42', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Partner', 'slug' => 'partner', 'color' => '#10b981', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Konkurencja', 'slug' => 'konkurencja', 'color' => '#ef4444', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Add customer_type_id to crm_companies
        Schema::table('crm_companies', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_type_id')->nullable()->after('type');
            $table->foreign('customer_type_id')->references('id')->on('crm_customer_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_companies', function (Blueprint $table) {
            $table->dropForeign(['customer_type_id']);
            $table->dropColumn('customer_type_id');
        });
        Schema::dropIfExists('crm_customer_types');
    }
};
