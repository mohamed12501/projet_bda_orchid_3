<?php

namespace Database\Seeders;

use App\Models\Faculte;
use App\Models\Universite;
use Illuminate\Database\Seeder;

class FaculteSeeder extends Seeder
{
    public function run(): void
    {
        $univ = Universite::first();
        
        Faculte::create([
            'nom' => 'Faculté des Sciences et Technologies',
            'id_univ' => $univ->id_univ,
        ]);
    }
}
