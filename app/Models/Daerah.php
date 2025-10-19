<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'daerah';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'kod_negeri',
        'kod_parlimen',
        'kod_dun',
        'kod_daerah',
        'nama_daerah',
    ];
}
