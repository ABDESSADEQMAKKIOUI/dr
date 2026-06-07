<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            SystemDataSeeder::class,
            AdminUserSeeder::class,
            UnitSeeder::class,
            SmsTemplateSeeder::class,
            // DemoDataSeeder::class, // Décommenter pour données de démonstration
        ]);
    }
}
