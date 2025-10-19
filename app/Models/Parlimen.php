<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parlimen extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'parlimen';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'kod_parlimen',
        'nama_parlimen',
        'kod_negeri',
    ];
}
