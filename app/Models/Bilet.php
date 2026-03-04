<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bilet extends Model
{
    protected $table = 'bilete';
    protected $primaryKey = 'id_bilet';
    public $timestamps = false;

    protected $fillable = [
        'id_client',
        'id_proiectie',
        'loc_sala',
        'pret',
        'data_cumparare',
    ];
}
