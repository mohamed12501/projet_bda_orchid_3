<?php

namespace Database\Seeders;

use App\Models\Etudiant;
use App\Models\Formation;
use App\Models\Section;
use App\Models\Groupe;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EtudiantSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $formations = Formation::all();
        
        // Generate 500+ students
        for ($i = 0; $i < 550; $i++) {
            $formation = $formations->random();
            
            // Get sections for this formation
            $sections = Section::where('id_formation', $formation->id_formation)->get();
            
            $section = null;
            $groupe = null;
            
            // Assign section and group if available
            if ($sections->isNotEmpty()) {
                $section = $sections->random();
                $groupes = Groupe::where('id_section', $section->id_section)->get();
                
                if ($groupes->isNotEmpty()) {
                    $groupe = $groupes->random();
                }
            }
            
            Etudiant::create([
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'date_naissance' => $faker->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
                'email' => $faker->unique()->safeEmail,
                'promo' => rand(2020, 2024),
                'id_formation' => $formation->id_formation,
                'section_id' => $section?->id_section,
                'group_id' => $groupe?->id_groupe,
            ]);
        }
    }
}
