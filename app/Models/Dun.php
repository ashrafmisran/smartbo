<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'dun';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'kod_negeri',
        'kod_parlimen',
        'kod_dun',
        'nama_dun',
    ];
}
