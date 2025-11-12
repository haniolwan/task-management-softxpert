<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        $createTasks = Permission::firstOrCreate(['name' => 'create tasks']);
        $updateTasks = Permission::firstOrCreate(['name' => 'update tasks']);
        $assignTasks = Permission::firstOrCreate(['name' => 'assign tasks']);

        $listTasks = Permission::firstOrCreate(['name' => 'list tasks']);
        $editTask = Permission::firstOrCreate(['name' => 'edit tasks']);

        $managerRole->givePermissionTo([$createTasks, $updateTasks, $assignTasks]);
        $userRole->givePermissionTo($listTasks, $editTask);

        $user = User::find(1);
        $user->assignRole('manager');
    }
}
