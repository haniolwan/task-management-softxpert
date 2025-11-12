<?php

namespace Database\Seeders;

use App\Models\User;
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
            'name' => 'Manager 1',
            'email' => 'manager1@example.com',
        ]);

        User::factory()->create([
            'name' => 'Manager 2',
            'email' => 'manager2@example.com',
            'role' => 'manager'
        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'role' => 'user'
        ]);
    }
}
