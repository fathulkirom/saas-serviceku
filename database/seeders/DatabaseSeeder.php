<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            SystemSettingSeeder::class,
        ]);

        // Buat tenant demo untuk testing
        if (app()->environment('local', 'development')) {
            $this->call(DemoTenantSeeder::class);
        }
    }
}
