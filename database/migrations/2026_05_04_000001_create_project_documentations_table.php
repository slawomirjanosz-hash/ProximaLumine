<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_documentations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->unique();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('template_path')->nullable();
            $table->string('tytul')->nullable();
            $table->string('numer_dokumentu')->nullable();
            $table->date('data_dokumentu')->nullable();
            $table->string('autor')->nullable();
            $table->string('branza')->nullable();
            $table->string('inwestor')->nullable();
            $table->text('inwestor_adres')->nullable();
            $table->string('inwestor_nip')->nullable();
            $table->text('adres_inwestycji')->nullable();
            $table->string('nr_pozwolenia')->nullable();
            $table->text('przedmiot_zakresu')->nullable();
            $table->text('opis_techniczny')->nullable();
            $table->text('materialy_urzadzenia')->nullable();
            $table->text('parametry_techniczne')->nullable();
            $table->text('normy_przepisy')->nullable();
            $table->text('warunki_gwarancji')->nullable();
            $table->text('warunki_odbioru')->nullable();
            $table->text('uwagi')->nullable();
            $table->text('zalaczniki')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documentations');
    }
};
