<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Compte administrateur par défaut : à changer après le premier test.
        User::firstOrCreate(
            ['email' => 'admin@patrimoine.tg'],
            ['name' => 'Administrateur', 'password' => 'Admin@2026', 'role' => 'admin']
        );
    }
}