<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokaliti extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'lokaliti';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Lokaliti',
        'Nama_Lokaliti',
        'Kod_DUN',
        'Kod_Daerah',
        'Kod_Negeri',
        'Kod_Parlimen',
    ];
}
