<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kawasan extends Model
{
    protected $table = 'kawasan';

    protected $fillable = [
        'id',
        'name',
        'negeri',
    ];
}
