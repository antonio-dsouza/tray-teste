<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seller;
use App\Models\Sale;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@teste-tray.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@teste-tray.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole('manager');

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@teste-tray.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password'),
            ]
        );
        $viewer->assignRole('viewer');

        if (Seller::count() === 0) {
            Seller::factory()
                ->count(5)
                ->has(Sale::factory()->count(10))
                ->create();
        }
    }
}
