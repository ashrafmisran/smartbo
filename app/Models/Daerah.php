<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'daerah';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Negeri',
        'Kod_Parlimen',
        'Kod_DUN',
        'Kod_Daerah',
        'Nama_Daerah',
    ];

    /**
     * Get the negeri that this daerah belongs to
     */
    public function negeri()
    {
        return $this->belongsTo(Negeri::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    /**
     * Get the parlimen that this daerah belongs to
     */
    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'Kod_Parlimen', 'Kod_Parlimen');
    }

    /**
     * Get the DUN that this daerah belongs to
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class, 'Kod_DUN', 'Kod_DUN');
    }

    /**
     * Get the lokalitis that belong to this daerah
     * This uses composite key matching (DUN + Daerah)
     */
    public function lokalitis()
    {
        return $this->hasMany(Lokaliti::class, 'Kod_DUN', 'Kod_DUN')
            ->where('lokaliti.Kod_Daerah', $this->Kod_Daerah);
    }

    /**
     * Get lokalitis using method for better performance
     */
    public function getLokalitis()
    {
        return Lokaliti::where('Kod_DUN', $this->Kod_DUN)
            ->where('Kod_Daerah', $this->Kod_Daerah)
            ->get();
    }

    /**
     * Get the pengundis that belong to this daerah
     * This uses composite key matching with zero-padding conversion
     */
    public function getPengundis()
    {
        return Pengundi::where('Kod_DUN', ltrim($this->Kod_DUN, '0') ?: '0')
            ->where('Kod_Daerah', ltrim($this->Kod_Daerah, '0') ?: '0')
            ->get();
    }

    /**
     * Count lokalitis in this daerah
     */
    public function getLokalitisCountAttribute()
    {
        return Lokaliti::where('Kod_DUN', $this->Kod_DUN)
            ->where('Kod_Daerah', $this->Kod_Daerah)
            ->count();
    }

    /**
     * Count pengundis in this daerah
     */
    public function getPengundiCountAttribute()
    {
        return Pengundi::where('Kod_DUN', ltrim($this->Kod_DUN, '0') ?: '0')
            ->where('Kod_Daerah', ltrim($this->Kod_Daerah, '0') ?: '0')
            ->count();
    }

    /**
     * Get full location hierarchy as string
     */
    public function getFullLocationAttribute()
    {
        $parts = [];
        
        if ($this->negeri) {
            $parts[] = $this->negeri->Nama_Negeri;
        }
        if ($this->parlimen) {
            $parts[] = $this->parlimen->Nama_Parlimen;
        }
        if ($this->dun) {
            $parts[] = $this->dun->Nama_DUN;
        }
        $parts[] = $this->Nama_Daerah;
        
        return implode(' → ', $parts);
    }

    /**
     * Scope to filter by DUN
     */
    public function scopeByDun($query, $dunCode)
    {
        return $query->where('Kod_DUN', str_pad($dunCode, 2, '0', STR_PAD_LEFT));
    }

    /**
     * Scope to filter by Negeri
     */
    public function scopeByNegeri($query, $negeriCode)
    {
        return $query->where('Kod_Negeri', str_pad($negeriCode, 2, '0', STR_PAD_LEFT));
    }

    /**
     * Scope to filter by Parlimen
     */
    public function scopeByParlimen($query, $parlimenCode)
    {
        return $query->where('Kod_Parlimen', str_pad($parlimenCode, 3, '0', STR_PAD_LEFT));
    }
}
