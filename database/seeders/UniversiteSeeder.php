<?php

namespace Database\Seeders;

use App\Models\Universite;
use Illuminate\Database\Seeder;

class UniversiteSeeder extends Seeder
{
    public function run(): void
    {
        Universite::create([
            'nom' => 'Université de Technologie',
            'ville' => 'Paris',
        ]);
    }
}
