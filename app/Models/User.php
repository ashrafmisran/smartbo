<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use App\Models\Database;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser //implements HasTenants
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pas_membership_no',
        'division',
        'status',
        'is_admin',
        'is_superadmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function databases()
    {
        return $this->belongsToMany(Database::class, 'database_user');
    }

    public function division()
    {
        return $this->belongsTo(Kawasan::class, 'division', 'id');
    }

    public function getTenants(Panel $panel = null): Collection
    {
        return $this->databases;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->databases()->whereKey($tenant->getKey())->exists(); // fix: compare by key
    }

    public function canAccessFilament(): bool
    {
        return true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status != 'suspended' || $this->status != 'pending';
    }

}
