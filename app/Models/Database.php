<?php

namespace App\Models;

use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Database extends Model implements HasCurrentTenantLabel
{
    protected $fillable = [
        'name',
        'alias',
        'host',
        'port',
        'username',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Encrypt/decrypt the password attribute
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Crypt::decryptString($value),
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }

    /**
     * Get decrypted password for database connections
     */
    public function getDecryptedPassword(): string
    {
        return $this->password;
    }

    /**
     * Create a dynamic database connection
     */
    public function createConnection(string $database = null): array
    {
        return [
            'driver' => 'mysql',
            'host' => $this->host,
            'port' => $this->port,
            'database' => $database ?? $this->name,
            'username' => $this->username,
            'password' => $this->getDecryptedPassword(),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'database_user');
    }

    public function getCurrentTenantLabel(): string
    {
        return $this->alias ?? $this->name;
    }
}
