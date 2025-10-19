<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negeri extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'negeri';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'kod_negeri',
        'nama_negeri',
    ];
}
