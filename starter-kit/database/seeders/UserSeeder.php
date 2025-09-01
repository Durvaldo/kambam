<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@test.com',
            'password' => Hash::make('password'),
        ]);
    }
}