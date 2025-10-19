<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengundi extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'daftara';

    // No actual primary key in database, but we'll use No_KP_Baru as unique identifier
    protected $primaryKey = 'No_KP_Baru';
    protected $keyType = 'string';
    public $incrementing = false;
    
    // Disable timestamps if the table doesn't have created_at/updated_at columns
    public $timestamps = false;

    protected $fillable = [
        'No_KP_Baru',
        'Nama',
        'Kod_Negeri',
        'Kod_Parlimen',
        'Kod_DUN',
        'Kod_Daerah',
        'Kod_Lokaliti',
        'Keturunan',
        'Bangsa',
        'Agama',
        'Kod_Cula',
        'Catatan',
    ];

    /**
     * Get the route key for the model.
     * Since there's no primary key, use No_KP_Baru as the route key.
     */
    public function getRouteKeyName()
    {
        return 'No_KP_Baru';
    }

    /**
     * Override getKey to return No_KP_Baru since it's our unique identifier
     */
    public function getKey()
    {
        return $this->getAttribute('No_KP_Baru');
    }
    
    public function negeri()
    {
        return $this->belongsTo(Negeri::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'Kod_Parlimen', 'Kod_Parlimen');
    }

    public function dun()
    {
        return $this->belongsTo(Dun::class, 'Kod_DUN', 'Kod_DUN');
    }

    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'Kod_Daerah', 'Kod_Daerah')
            ->where('Kod_DUN', $this->Kod_DUN);
    }

    public function lokaliti()
    {
        return $this->belongsTo(Lokaliti::class, 'Kod_Lokaliti', 'Kod_Lokaliti')
            ->where('Kod_DUN', $this->Kod_DUN)
            ->where('Kod_Daerah', $this->Kod_Daerah);
    }

    public function scopeWithLokaliti($query)
    {
        return $query->join('lokaliti', function ($join) {
            $join->on('lokaliti.Kod_Lokaliti', '=', 'daftara.Kod_Lokaliti')
                ->on('lokaliti.Kod_Daerah',   '=', 'daftara.Kod_Daerah')
                ->on('lokaliti.Kod_DUN',      '=', 'daftara.Kod_DUN');
        })->addSelect('daftara.*', 'lokaliti.Nama as Nama_Lokaliti');
    }

}
