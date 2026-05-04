<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDocumentation extends Model
{
    protected $table = 'project_documentations';

    protected $fillable = [
        'project_id',
        'template_path',
        'tytul',
        'numer_dokumentu',
        'data_dokumentu',
        'autor',
        'branza',
        'inwestor',
        'inwestor_adres',
        'inwestor_nip',
        'adres_inwestycji',
        'nr_pozwolenia',
        'przedmiot_zakresu',
        'opis_techniczny',
        'materialy_urzadzenia',
        'parametry_techniczne',
        'normy_przepisy',
        'warunki_gwarancji',
        'warunki_odbioru',
        'uwagi',
        'zalaczniki',
    ];

    protected $casts = [
        'data_dokumentu' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
