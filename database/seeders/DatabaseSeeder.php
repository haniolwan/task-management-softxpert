<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RolesAndPermissionsSeeder::class
        ]);

        $managerRole = Role::findByName('manager');
        $userRole = Role::findByName('user');

        $firstManager = User::factory()->create([
            'name' => 'Manager 1',
            'email' => 'manager1@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $firstManager->assignRole('manager');

        $secondManager = User::factory()->create([
            'name' => 'Manager 2',
            'email' => 'manager2@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $secondManager->assignRole($managerRole);

        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $user->assignRole($userRole);
    }
}
