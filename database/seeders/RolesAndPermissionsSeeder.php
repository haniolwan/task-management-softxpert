<?php

namespace Database\Seeders;

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
        $managerRole = Role::firstOrCreate(['name' => 'manager'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        $createTasks = Permission::firstOrCreate(['name' => 'create tasks']);
        $updateTasks = Permission::firstOrCreate(['name' => 'update tasks']);
        $assignTasks = Permission::firstOrCreate(['name' => 'assign tasks']);


        $listTasks = Permission::firstOrCreate(['name' => 'list tasks']);
        $editStatus = Permission::firstOrCreate(['name' => 'update task status']);

        $managerRole->givePermissionTo([$createTasks, $updateTasks, $assignTasks, $listTasks]);
        $userRole->givePermissionTo([$listTasks, $editStatus]);
    }
}
