<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Faculte;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $fac = Faculte::first();
        
        $departements = [
            ['nom' => 'Informatique', 'lieu' => 'Bâtiment A'],
            ['nom' => 'Mathématiques', 'lieu' => 'Bâtiment B'],
            ['nom' => 'Physique', 'lieu' => 'Bâtiment C'],
            ['nom' => 'Chimie', 'lieu' => 'Bâtiment D'],
            ['nom' => 'Biologie', 'lieu' => 'Bâtiment E'],
            ['nom' => 'Électronique', 'lieu' => 'Bâtiment F'],
            ['nom' => 'Mécanique', 'lieu' => 'Bâtiment G'],
        ];
        
        foreach ($departements as $dept) {
            Departement::create([
                'nom' => $dept['nom'],
                'lieu' => $dept['lieu'],
                'id_fac' => $fac->id_fac,
            ]);
        }
    }
}
