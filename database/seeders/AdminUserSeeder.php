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
        $email = env('ADMIN_SEED_EMAIL');
        $password = env('ADMIN_SEED_PASSWORD');

        if (empty($email) || empty($password)) {
            throw new \RuntimeException(
                'ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD must both be set in .env before running db:seed.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_SEED_NAME', 'Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
