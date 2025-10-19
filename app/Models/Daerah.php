<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'daerah';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Negeri',
        'Kod_Parlimen',
        'Kod_DUN',
        'Kod_Daerah',
        'Nama_Daerah',
    ];
}
