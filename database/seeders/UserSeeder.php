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
            ['email' => 'wilmer@water.quality.com'],
            [
                'name' => 'Wilmer Iriarte Camargo',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'jesus@water.quality.com'],
            [
                'name' => 'Jesus Caraballo Nieto',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'isaias@water.quality.com'],
            [
                'name' => 'Isaias Gamarra Cardona',
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
