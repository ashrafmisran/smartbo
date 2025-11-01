<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parlimen extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'parlimen';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Parlimen',
        'Nama_Parlimen',
        'Kod_Negeri',
    ];

    // BelongsTo relationships
    public function negeri()
    {
        return $this->belongsTo(Negeri::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    // HasMany relationships (composite via whereColumn)
    public function duns()
    {
        return $this->hasMany(Dun::class, 'Kod_Parlimen', 'Kod_Parlimen')
            ->whereColumn('dun.Kod_Negeri', 'parlimen.Kod_Negeri');
    }

    public function daerahs()
    {
        return $this->hasMany(Daerah::class, 'Kod_Parlimen', 'Kod_Parlimen')
            ->whereColumn('daerah.Kod_Negeri', 'parlimen.Kod_Negeri');
    }

    public function lokalitis()
    {
        return $this->hasMany(Lokaliti::class, 'Kod_Parlimen', 'Kod_Parlimen')
            ->whereColumn('lokaliti.Kod_Negeri', 'parlimen.Kod_Negeri');
    }

    public function pengundis()
    {
        return $this->hasMany(Pengundi::class, 'Kod_Parlimen', 'Kod_Parlimen')
            ->whereColumn('daftara.Kod_Negeri', 'parlimen.Kod_Negeri');
    }

    // Custom methods for relationships
    public function getDuns()
    {
        return Dun::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->get();
    }

    public function getDaerahs()
    {
        return Daerah::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->get();
    }

    public function getLokalitis()
    {
        return Lokaliti::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->get();
    }

    public function getPengundis()
    {
        return Pengundi::where('Kod_Negeri', $this->Kod_Negeri)
            ->where('Kod_Parlimen', $this->Kod_Parlimen)
            ->get();
    }

    // Accessors for counts and display (kept for compatibility if used elsewhere)
    public function getDunsCountAttribute()
    {
        return $this->relationLoaded('duns') ? $this->duns->count() : $this->getDuns()->count();
    }

    public function getDaerahsCountAttribute()
    {
        return $this->relationLoaded('daerahs') ? $this->daerahs->count() : $this->getDaerahs()->count();
    }

    public function getLokalitisCountAttribute()
    {
        return $this->relationLoaded('lokalitis') ? $this->lokalitis->count() : $this->getLokalitis()->count();
    }

    public function getPengundisCountAttribute()
    {
        return $this->relationLoaded('pengundis') ? $this->pengundis->count() : $this->getPengundis()->count();
    }

    public function getFullLocationAttribute()
    {
        $negeri = $this->negeri ? $this->negeri->Nama_Negeri : 'Unknown';
        
        return "{$negeri} → {$this->Nama_Parlimen}";
    }

    // Query scopes
    public function scopeByNegeri($query, $kod_negeri)
    {
        return $query->where('Kod_Negeri', $kod_negeri);
    }

    public function scopeWithCounts($query)
    {
        return $query->selectRaw('parlimen.*, 
            (SELECT COUNT(*) FROM dun 
             WHERE dun.Kod_Negeri = parlimen.Kod_Negeri 
             AND dun.Kod_Parlimen = parlimen.Kod_Parlimen) as duns_count,
            (SELECT COUNT(*) FROM daerah 
             WHERE daerah.Kod_Negeri = parlimen.Kod_Negeri 
             AND daerah.Kod_Parlimen = parlimen.Kod_Parlimen) as daerahs_count,
            (SELECT COUNT(*) FROM lokaliti 
             WHERE lokaliti.Kod_Negeri = parlimen.Kod_Negeri 
             AND lokaliti.Kod_Parlimen = parlimen.Kod_Parlimen) as lokalitis_count,
            (SELECT COUNT(*) FROM daftara 
             WHERE daftara.Kod_Negeri = parlimen.Kod_Negeri 
             AND daftara.Kod_Parlimen = parlimen.Kod_Parlimen) as pengundis_count');
    }
}
