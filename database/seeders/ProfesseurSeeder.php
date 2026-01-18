<?php

namespace Database\Seeders;

use App\Models\Professeur;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProfesseurSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $departements = Departement::all();
        $grades = ['Professeur', 'Maître de Conférences', 'Chargé de Cours', 'Assistant'];
        
        // Generate 60+ professors
        for ($i = 0; $i < 65; $i++) {
            $dept = $departements->random();
            
            Professeur::create([
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'date_naissance' => $faker->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'),
                'email' => $faker->unique()->safeEmail,
                'grade' => $grades[array_rand($grades)],
                'id_dept' => $dept->id_dept,
                'nb_surveillances_periode' => 0,
            ]);
        }
    }
}
