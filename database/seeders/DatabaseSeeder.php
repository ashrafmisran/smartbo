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

        $this->call(KawasanCsvSeeder::class);

        User::factory()->create([
            'name' => 'Muhammad Ashraf bin Misran',
            'email' => 'ashrafmisran@gmail.com',
            'password' => bcrypt(env('DEFAULT_ADMIN_PASSWORD', 'password')),
            'pas_membership_no' => '1040820',
            'division' => 128,
            'status' => 'verified',
            'is_admin' => true,
            'is_superadmin' => true,
        ]);

    }
}
