<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Database;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Muhammad Ashraf bin Misran',
            'email' => 'ashrafmisran@gmail.com',
            'password' => bcrypt(env('DEFAULT_ADMIN_PASSWORD', 'password')),
        ]);

        // Seed sample databases
        Database::create([
            'name' => 'production_db',
            'alias' => 'Production Database',
            'host' => 'prod.example.com',
            'port' => 3306,
            'username' => 'prod_user',
            'password' => 'production_password_123',
        ]);

        Database::create([
            'name' => 'staging_db', 
            'alias' => 'Staging Environment',
            'host' => 'staging.example.com',
            'port' => 3306,
            'username' => 'staging_user',
            'password' => 'staging_password_456',
        ]);

        Database::create([
            'name' => 'analytics_db',
            'alias' => 'Analytics & Reporting',
            'host' => 'analytics.example.com',
            'port' => 3306,
            'username' => 'analytics_user', 
            'password' => 'analytics_password_789',
        ]);

        Database::create([
            'name' => 'backup_db',
            'alias' => 'Backup Database',
            'host' => 'backup.example.com',
            'port' => 3307,
            'username' => 'backup_user',
            'password' => 'backup_password_000',
        ]);

        // Attach admin user to all databases
        $adminUser = User::where('email', 'ashrafmisran@gmail.com')->first();
        $databases = Database::all();
        $adminUser->databases()->sync($databases->pluck('id')->toArray());
    }
}
