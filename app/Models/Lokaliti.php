<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokaliti extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'lokaliti';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'kod_lokaliti',
        'nama_lokaliti',
        'kod_dun',
    ];
}
