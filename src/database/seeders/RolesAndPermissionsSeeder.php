<?php 

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'create_ticket',
            'assign_leader',
            'assign_task', 
            'submit_estimation',
            'review_estimation',
            'approve_estimation',
            'request_estimation_change',
            'send_plan_to_client',
            'client_approve_plan',
            'start_execution',
            'log_progress',
            'mark_task_complete',
            'review_execution',
            'close_ticket',
            'comment_ticket',
            'comment_task',
            'view_all_tickets',
            'view_ticket',
            'invite_user_to_ticket',
            'read_client_feedback',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and assign permissions
        $roles = [
            'admin' => $permissions, // Admin gets all
            'supporter' => [
                'create_ticket',
                'assign_leader',
                'send_plan_to_client',
                'client_approve_plan',
                'comment_ticket',
                'read_client_feedback',
                'invite_user_to_ticket',
                'view_ticket',
            ],
            'leader' => [
                'assign_task',
                'review_estimation',
                'approve_estimation',
                'request_estimation_change',
                'review_execution',
                'close_ticket',
                'comment_task',
                'view_ticket',
                'invite_user_to_ticket',
            ],
            'staff' => [
                'submit_estimation',
                'log_progress',
                'mark_task_complete',
                'comment_task',
                'comment_ticket',
                'view_ticket',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Create default users
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com', 
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $anotherAdmin = User::create([
            'name' => 'phamphanbang',
            'email' => 'phamphanbang@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $anotherAdmin->assignRole('admin');
    }
}
