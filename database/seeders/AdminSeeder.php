<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@rapidinsightdesigns.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Staff Member',
            'email'    => 'staff@rapidinsightdesigns.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);

        User::create([
            'name'     => 'Demo Customer',
            'email'    => 'customer@example.com',
            'password' => Hash::make('password'),
            'role'     => 'customer',
            'company'  => 'Demo Corp',
        ]);
    }
}
