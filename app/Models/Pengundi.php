<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Get the daerah record based on composite key matching (DUN + Daerah)
     */
    public function getDaerah()
    {
        return Daerah::where('Kod_DUN', str_pad($this->Kod_DUN, 2, '0', STR_PAD_LEFT))
            ->where('Kod_Daerah', str_pad($this->Kod_Daerah, 2, '0', STR_PAD_LEFT))
            ->first();
    }
    
    /**
     * Accessor for getting daerah name
     */
    public function getNamaDaerahAttribute()
    {
        $daerah = $this->getDaerah();
        return $daerah ? $daerah->Nama_Daerah : null;
    }

    /**
     * Get the lokaliti record based on composite key matching
     */
    public function getLokaliti()
    {
        return Lokaliti::where('Kod_DUN', str_pad($this->Kod_DUN, 2, '0', STR_PAD_LEFT))
            ->where('Kod_Daerah', str_pad($this->Kod_Daerah, 2, '0', STR_PAD_LEFT))
            ->where('Kod_Lokaliti', str_pad($this->Kod_Lokaliti, 3, '0', STR_PAD_LEFT))
            ->first();
    }
    
    /**
     * Accessor for getting lokaliti name
     */
    public function getNamaLokalitaAttribute()
    {
        $lokaliti = $this->getLokaliti();
        return $lokaliti ? $lokaliti->Nama_Lokaliti : null;
    }

}
