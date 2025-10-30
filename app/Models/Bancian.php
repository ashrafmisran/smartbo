<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bancian extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'bancian';

    // No actual primary key in database, but we'll use No_KP_Baru as unique identifier
    protected $primaryKey = 'No_KP_Baru';
    protected $keyType = 'string';
    public $incrementing = false;
    
    // Disable timestamps if the table doesn't have created_at/updated_at columns
    public $timestamps = false;

    protected $fillable = [
        'No_KP_Baru',
        'Tel_Rumah',
        'Tel_Bimbit',
        'Catatan',
    ];

    /**
     * The columns that are allowed to be selected (due to database permissions)
     */
    protected $allowedColumns = [
        'No_KP_Baru',
        'Tel_Rumah',
        'Tel_Bimbit',
        'Catatan',
    ];

    

    /**
     * Override newQuery to always select only allowed columns
     */
    public function newQuery()
    {
        return parent::newQuery()->select($this->allowedColumns);
    }

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

    public function pengundi()
    {
        return $this->belongsTo(Pengundi::class, 'No_KP_Baru', 'No_KP_Baru');
    }
}
