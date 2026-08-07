<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding production demo data...');

        // Demo Users
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@serviceku.test', 'password' => Hash::make('password'), 'role' => 'owner'],
            ['name' => 'Owner Demo', 'email' => 'owner@serviceku.test', 'password' => Hash::make('password'), 'role' => 'owner'],
            ['name' => 'Manager Demo', 'email' => 'manager@serviceku.test', 'password' => Hash::make('password'), 'role' => 'manager'],
            ['name' => 'CS Demo', 'email' => 'cs@serviceku.test', 'password' => Hash::make('password'), 'role' => 'cs'],
            ['name' => 'Teknisi Demo', 'email' => 'teknisi@serviceku.test', 'password' => Hash::make('password'), 'role' => 'technician'],
            ['name' => 'Gudang Demo', 'email' => 'gudang@serviceku.test', 'password' => Hash::make('password'), 'role' => 'head_store'],
            ['name' => 'Kasir Demo', 'email' => 'kasir@serviceku.test', 'password' => Hash::make('password'), 'role' => 'cashier'],
            ['name' => 'Finance Demo', 'email' => 'finance@serviceku.test', 'password' => Hash::make('password'), 'role' => 'admin'],
        ];

        foreach ($users as $user) {
            if (!DB::table('users')->where('email', $user['email'])->exists()) {
                DB::table('users')->insert(array_merge($user, [
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $this->command->line("  User: {$user['email']}");
            }
        }

        // Demo Customers
        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'budi@example.com'],
            ['name' => 'Siti Nurhaliza', 'phone' => '081234567891', 'email' => 'siti@example.com'],
            ['name' => 'Ahmad Dhani', 'phone' => '081234567892', 'email' => 'ahmad@example.com'],
        ];
        foreach ($customers as $c) {
            if (!DB::table('customers')->where('phone', $c['phone'])->exists()) {
                DB::table('customers')->insert(array_merge($c, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
        $this->command->info('Demo data seeded successfully.');
    }
}
