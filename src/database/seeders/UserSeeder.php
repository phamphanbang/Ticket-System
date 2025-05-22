<?php

namespace Database\Seeders;

use App\Constants\UserRoles;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('role','admin')->first();
        $staffRole = Role::where('role', 'staff')->first();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::ADMIN->value,
        ]);

        User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::STAFF->value,
        ]);

        User::create([
            'name' => 'phamphanbang',
            'email' => 'phamphanbang@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::ADMIN->value,
        ]);
    }
}
