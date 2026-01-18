<?php

namespace Database\Seeders;

use App\Models\Salle;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $batiments = ['A', 'B', 'C', 'D', 'E'];
        $types = ['salle', 'amphi'];
        
        // Generate 30 rooms
        for ($i = 1; $i <= 30; $i++) {
            $type = $i <= 5 ? 'amphi' : 'salle'; // First 5 are amphitheaters
            $capacite = $type === 'amphi' ? rand(200, 500) : rand(30, 100);
            $capaciteExamen = (int)($capacite * 0.8); // 80% capacity for exams
            
            Salle::create([
                'nom' => "Salle {$i}",
                'capacite' => $capacite,
                'type' => $type,
                'batiment' => $batiments[array_rand($batiments)],
                'capacite_normale' => $capacite,
                'capacite_examen' => $capaciteExamen,
            ]);
        }
    }
}
