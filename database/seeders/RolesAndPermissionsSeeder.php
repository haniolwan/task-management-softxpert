<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = Role::firstOrCreate(['name' => 'manager'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        // Create permissions
        $createTasks = Permission::firstOrCreate(['name' => 'create tasks', 'guard_name' => 'web']);
        $updateTasks = Permission::firstOrCreate(['name' => 'update tasks', 'guard_name' => 'web']);
        $assignTasks = Permission::firstOrCreate(['name' => 'assign tasks', 'guard_name' => 'web']);
        $listTasks = Permission::firstOrCreate(['name' => 'list tasks', 'guard_name' => 'web']);
        $editTask = Permission::firstOrCreate(['name' => 'edit tasks', 'guard_name' => 'web']);

        // Assign permissions to roles
        $managerRole->givePermissionTo([$createTasks, $updateTasks, $assignTasks]);
        $userRole->givePermissionTo([$listTasks, $editTask]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign role to first user
        $user = User::find(1);
        if ($user) {
            $user->assignRole($managerRole);
        }
    }
}
