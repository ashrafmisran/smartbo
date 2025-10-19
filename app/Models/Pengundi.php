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
}
