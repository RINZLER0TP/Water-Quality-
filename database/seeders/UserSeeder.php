<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@water-quality.test'],
            [
                'name' => 'Admin Water Quality',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@water-quality.test'],
            [
                'name' => 'User Water Quality',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );
    }
}
