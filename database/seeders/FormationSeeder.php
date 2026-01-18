<?php

namespace Database\Seeders;

use App\Models\Formation;
use App\Models\Departement;
use Illuminate\Database\Seeder;

class FormationSeeder extends Seeder
{
    public function run(): void
    {
        $departements = Departement::all();
        $niveaux = ['L', 'M', 'D'];
        
        foreach ($departements as $dept) {
            foreach ($niveaux as $niveau) {
                Formation::create([
                    'nom' => "Licence {$dept->nom}",
                    'niveau' => $niveau,
                    'nb_modules' => rand(6, 9),
                    'id_dept' => $dept->id_dept,
                ]);
            }
        }
    }
}
