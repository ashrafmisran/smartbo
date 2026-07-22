<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Pengundi;

class CallRecord extends Model
{
    protected $fillable = [
        'user_id',
        'pengundi_ic',
        'phone_number',
        'kod_cula',
        'notes',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pengundi(): BelongsTo
    {
        return $this->belongsTo(Pengundi::class, 'pengundi_ic', 'No_KP_Baru');
    }
}
