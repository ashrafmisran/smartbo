<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'dun';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Negeri',
        'Kod_Parlimen', 
        'Kod_DUN',
        'Nama_DUN',
    ];

    // BelongsTo relationships
    public function negeri()
    {
        return $this->belongsTo(Negeri::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'Kod_Parlimen', 'Kod_Parlimen');
    }

    // Custom methods for relationships that use other models
    public function getDaerahs()
    {
        return Daerah::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->where('Kod_DUN', $this->Kod_DUN)
            ->get();
    }

    public function getLokalitis()
    {
        return Lokaliti::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->where('Kod_DUN', $this->Kod_DUN)
            ->get();
    }

    public function getPengundis()
    {
        return Pengundi::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->where('Kod_DUN', $this->Kod_DUN)
            ->get();
    }

    // Accessors for counts and display
    public function getDaerahsCountAttribute()
    {
        return $this->getDaerahs()->count();
    }

    public function getLokalitisCountAttribute()
    {
        return $this->getLokalitis()->count();
    }

    public function getPengundisCountAttribute()
    {
        return $this->getPengundis()->count();
    }

    public function getFullLocationAttribute()
    {
        $negeri = $this->negeri ? $this->negeri->Nama_Negeri : 'Unknown';
        $parlimen = $this->parlimen ? $this->parlimen->Nama_Parlimen : 'Unknown';
        
        return "{$negeri} → {$parlimen} → {$this->Nama_DUN}";
    }

    // Query scopes
    public function scopeByNegeri($query, $kod_negeri)
    {
        return $query->where('Kod_Negeri', $kod_negeri);
    }

    public function scopeByParlimen($query, $kod_parlimen)
    {
        return $query->where('Kod_Parlimen', $kod_parlimen);
    }

    public function scopeWithCounts($query)
    {
        return $query->selectRaw('dun.*, 
            (SELECT COUNT(*) FROM daerah 
             WHERE daerah.Kod_Negeri = dun.Kod_Negeri 
             AND daerah.Kod_Parlimen = dun.Kod_Parlimen
             AND daerah.Kod_DUN = dun.Kod_DUN) as daerahs_count,
            (SELECT COUNT(*) FROM lokaliti 
             WHERE lokaliti.Kod_Negeri = dun.Kod_Negeri 
             AND lokaliti.Kod_Parlimen = dun.Kod_Parlimen
             AND lokaliti.Kod_DUN = dun.Kod_DUN) as lokalitis_count,
            (SELECT COUNT(*) FROM daftara 
             WHERE daftara.Kod_Negeri = dun.Kod_Negeri 
             AND daftara.Kod_Parlimen = dun.Kod_Parlimen
             AND daftara.Kod_DUN = dun.Kod_DUN) as pengundis_count');
    }
}
