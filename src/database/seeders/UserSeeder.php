<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com', 
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create regular users
        User::factory()->count(10)->create([
            'role' => 'user',
            'password' => Hash::make('password'),
        ]);

        // Create specific test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user', 
        ]);
    }
}
