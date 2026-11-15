<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Direction générale', 'Finances', 'Secrétariat général', 'Archives', 'Logistique', 'Technique'] as $nom) {
            Service::firstOrCreate(['nom' => $nom]);
        }
    }
}