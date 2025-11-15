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
        //'Agama',
        'Kod_Cula',
        'Catatan',
    ];

    /**
     * The columns that are allowed to be selected (due to database permissions)
     */
    protected $allowedColumns = [
        'No_KP_Baru',
        'Nama',
        'Kod_Negeri',
        'Kod_Parlimen', 
        'Kod_DUN',
        'Kod_Daerah',
        'Kod_Lokaliti',
        'Keturunan',
        'Bangsa',
        //'Agama',
        'Kod_Cula',
        'Catatan',
    ];

    /**
     * Boot the model and add global scope for column selection
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('allowedColumns', function ($builder) {
            $builder->select([
                'No_KP_Baru',
                'Nama',
                'Kod_Negeri',
                'Kod_Parlimen', 
                'Kod_DUN',
                'Kod_Daerah',
                'Kod_Lokaliti',
                'Keturunan',
                'Bangsa',
                //'Agama',
                'Kod_Cula',
                'Catatan',
            ]);
        });
    }

    /**
     * Override newQuery to always select only allowed columns
     */
    public function newQuery()
    {
        return parent::newQuery()->select($this->allowedColumns);
    }

    /**
     * Override newQueryWithoutScopes to always select only allowed columns
     */
    public function newQueryWithoutScopes()
    {
        return parent::newQueryWithoutScopes()->select($this->allowedColumns);
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

    public function Tel_Bimbit()
    {
        return $this->hasOne(Bancian::class, 'No_KP_Baru', 'No_KP_Baru')
            ->select(['No_KP_Baru', 'Tel_Bimbit']);
    }

    public function Tel_Rumah(){
        return $this->hasOne(Bancian::class, 'No_KP_Baru', 'No_KP_Baru')
            ->select(['No_KP_Baru', 'Tel_Rumah']);
    }

    public function phone_numbers(){
        $numbers = [];
        array_merge($numbers, explode(',', $this->Tel_Bimbit()->pluck('Tel_Bimbit')->get() ?? []));
        array_merge($numbers, explode(',', $this->Tel_Rumah()->pluck('Tel_Rumah')->get() ?? []));
        return array_values(array_unique($numbers));
    }

    /**
     * Get the full Bancian record with phone numbers and catatan
     */
    public function bancian()
    {
        return $this->hasOne(Bancian::class, 'No_KP_Baru', 'No_KP_Baru')
            ->select(['No_KP_Baru', 'Tel_Bimbit', 'Tel_Rumah', 'Catatan']);
    }

    public function getCatatanAttribute(){
        // First check if Catatan exists in the pengundi record itself
        if (isset($this->attributes['Catatan']) && $this->attributes['Catatan'] !== null) {
            return $this->attributes['Catatan'];
        }
        
        // If not, try to get it from bancian
        $bancian = $this->bancian;
        return $bancian ? $bancian->Catatan : null;
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
    public function getNamaLokalitiAttribute()
    {
        $lokaliti = $this->getLokaliti();
        return $lokaliti ? $lokaliti->Nama_Lokaliti : null;
    }

    /**
     * Computed list of phone numbers from related Bancian rows (mobile first),
     * filtered to non-null, non-empty values and de-duplicated.
     */
    public function getPhoneNumbersAttribute(): array
    {
        $numbers = [];

        $b = implode(',', $this->Tel_Bimbit()->pluck('Tel_Bimbit')->toArray());
        $r = implode(',', $this->Tel_Rumah()->pluck('Tel_Rumah')->toArray());
        array_merge($numbers, explode(',', $b));
        array_merge($numbers, explode(',', $r));
        return array_values(array_unique($numbers));
    }

    /**
     * Convenience accessor: preferred/primary phone number (mobile if available, else home).
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        $list = $this->phone_numbers;
        return $list[0] ?? null;
    }

    // Cast phone number separated by commas into array
    protected $casts = [
        'phone_numbers' => 'array',
    ];

    /**
     * Get visible actions for Filament global search.
     * This method is called by Filament's global search blade template.
     */
    public function getVisibleActions(): array
    {
        return []; // No actions needed for global search results
    }

}
