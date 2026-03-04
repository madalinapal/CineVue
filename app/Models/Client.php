<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clienti';
    protected $primaryKey = 'id_client';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nume',
        'prenume',
        'data_nasterii',
        'nr_telefon',
        'email',
        'tip'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
