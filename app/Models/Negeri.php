<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negeri extends Model
{
    protected $connection = 'ssdp';
    protected $table = 'negeri';

    protected $primaryKey = 'REC_ID';

    protected $fillable = [
        'Kod_Negeri',
        'Nama_Negeri',
    ];

    // HasMany relationships
    public function parlimens()
    {
        return $this->hasMany(Parlimen::class, 'Kod_Negeri', 'Kod_Negeri');
    }

    // Custom methods for relationships
    public function getDuns()
    {
        return Dun::where('Kod_Negeri', $this->Kod_Negeri)->get();
    }

    public function getDaerahs()
    {
        return Daerah::where('Kod_Negeri', $this->Kod_Negeri)->get();
    }

    public function getLokalitis()
    {
        return Lokaliti::where('Kod_Negeri', $this->Kod_Negeri)->get();
    }

    public function getPengundis()
    {
        return Pengundi::where('Kod_Negeri', $this->Kod_Negeri)->get();
    }

    // Accessors for counts and display
    public function getParlimensCountAttribute()
    {
        return $this->parlimens()->count();
    }

    public function getDunsCountAttribute()
    {
        return $this->getDuns()->count();
    }

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
        return $this->Nama_Negeri;
    }

    // Query scopes
    public function scopeWithCounts($query)
    {
        return $query->selectRaw('negeri.*, 
            (SELECT COUNT(*) FROM parlimen 
             WHERE parlimen.Kod_Negeri = negeri.Kod_Negeri) as parlimens_count,
            (SELECT COUNT(*) FROM dun 
             WHERE dun.Kod_Negeri = negeri.Kod_Negeri) as duns_count,
            (SELECT COUNT(*) FROM daerah 
             WHERE daerah.Kod_Negeri = negeri.Kod_Negeri) as daerahs_count,
            (SELECT COUNT(*) FROM lokaliti 
             WHERE lokaliti.Kod_Negeri = negeri.Kod_Negeri) as lokalitis_count,
            (SELECT COUNT(*) FROM daftara 
             WHERE daftara.Kod_Negeri = negeri.Kod_Negeri) as pengundis_count');
    }
}
