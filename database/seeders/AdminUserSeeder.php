<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'moorejp@cua.edu'],
            [
                'name' => 'James Moore',
                'password' => Hash::make('ChangeMe2026!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
