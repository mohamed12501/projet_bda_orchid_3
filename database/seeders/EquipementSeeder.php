<?php

namespace Database\Seeders;

use App\Models\Equipement;
use App\Models\Salle;
use App\Models\SalleEquipement;
use Illuminate\Database\Seeder;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = [
            ['nom' => 'Ordinateurs', 'description' => 'Postes informatiques'],
            ['nom' => 'Projecteur', 'description' => 'Vidéoprojecteur'],
            ['nom' => 'Tableau Interactif', 'description' => 'Tableau blanc interactif'],
            ['nom' => 'Oscilloscope', 'description' => 'Appareil de mesure électronique'],
            ['nom' => 'Multimètre', 'description' => 'Appareil de mesure électrique'],
        ];
        
        foreach ($equipements as $eq) {
            Equipement::create($eq);
        }
        
        // Attach equipment to some rooms
        $salles = Salle::all();
        $equipements = Equipement::all();
        
        foreach ($salles->take(15) as $salle) {
            $equipement = $equipements->random();
            SalleEquipement::create([
                'id_salle' => $salle->id_salle,
                'id_equipement' => $equipement->id_equipement,
                'quantite' => rand(1, 5),
            ]);
        }
    }
}
