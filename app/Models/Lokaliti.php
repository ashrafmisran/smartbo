<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokaliti extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'lokaliti';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Lokaliti',
        'Nama_Lokaliti',
        'Kod_DUN',
        'Kod_Daerah',
        'Kod_Negeri',
        'Kod_Parlimen',
    ];

    /**
     * Get the negeri that this lokaliti belongs to
     */
    public function negeri()
    {
        return $this->belongsTo(Negeri::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    /**
     * Get the parlimen that this lokaliti belongs to
     */
    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'Kod_Parlimen', 'Kod_Parlimen');
    }

    /**
     * Get the DUN that this lokaliti belongs to
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class, 'Kod_DUN', 'Kod_DUN');
    }

    /**
     * Get the daerah that this lokaliti belongs to
     * This uses composite key matching (DUN + Daerah)
     */
    public function getDaerah()
    {
        return Daerah::where('Kod_DUN', $this->Kod_DUN)
            ->where('Kod_Daerah', $this->Kod_Daerah)
            ->first();
    }

    /**
     * Get the pengundis that belong to this lokaliti
     * This uses composite key matching with zero-padding
     */
    public function getPengundis()
    {
        return Pengundi::where('Kod_DUN', ltrim($this->Kod_DUN, '0') ?: '0')
            ->where('Kod_Daerah', ltrim($this->Kod_Daerah, '0') ?: '0')
            ->where('Kod_Lokaliti', ltrim($this->Kod_Lokaliti, '0') ?: '0')
            ->get();
    }

    /**
     * Accessor for daerah name
     */
    public function getNamaDaerahAttribute()
    {
        $daerah = $this->getDaerah();
        return $daerah ? $daerah->Nama_Daerah : null;
    }

    /**
     * Count pengundis in this lokaliti
     */
    public function getPengundiCountAttribute()
    {
        return Pengundi::where('Kod_DUN', ltrim($this->Kod_DUN, '0') ?: '0')
            ->where('Kod_Daerah', ltrim($this->Kod_Daerah, '0') ?: '0')
            ->where('Kod_Lokaliti', ltrim($this->Kod_Lokaliti, '0') ?: '0')
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
        if ($this->nama_daerah) {
            $parts[] = $this->nama_daerah;
        }
        $parts[] = $this->Nama_Lokaliti;
        
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
     * Scope to filter by Daerah (requires DUN as well)
     */
    public function scopeByDaerah($query, $dunCode, $daerahCode)
    {
        return $query->where('Kod_DUN', str_pad($dunCode, 2, '0', STR_PAD_LEFT))
                    ->where('Kod_Daerah', str_pad($daerahCode, 2, '0', STR_PAD_LEFT));
    }
}
