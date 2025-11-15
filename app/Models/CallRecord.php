<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Note: We don't create a relationship to Pengundi since we're using IC as string
    // but we can add a method to get pengundi data if needed
    public function getPengundiAttribute()
    {
        return \Illuminate\Support\Facades\DB::table('pengundi')
            ->where('No_KP_Baru', $this->pengundi_ic)
            ->first();
    }
}
