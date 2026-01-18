<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Formation;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $formations = Formation::all();
        $moduleNames = [
            'Algèbre Linéaire', 'Analyse', 'Probabilités', 'Statistiques',
            'Programmation', 'Base de Données', 'Réseaux', 'Systèmes d\'Exploitation',
            'Mécanique Quantique', 'Thermodynamique', 'Électromagnétisme',
            'Chimie Organique', 'Chimie Inorganique', 'Biochimie',
            'Génétique', 'Écologie', 'Anatomie',
            'Circuits Électroniques', 'Traitement du Signal', 'Microcontrôleurs',
            'Mécanique des Fluides', 'Résistance des Matériaux', 'Dessin Technique',
        ];
        
        foreach ($formations as $formation) {
            $nbModules = $formation->nb_modules ?? 7;
            $selectedModules = array_slice($moduleNames, 0, $nbModules);
            
            $preRequisId = null;
            foreach ($selectedModules as $index => $nom) {
                $necessiteEquipement = in_array($nom, ['Circuits Électroniques', 'Microcontrôleurs', 'Traitement du Signal']);
                
                $module = Module::create([
                    'nom' => "{$nom} - {$formation->nom}",
                    'credits' => rand(3, 6),
                    'id_formation' => $formation->id_formation,
                    'pre_requis_id' => $preRequisId,
                    'necessite_equipement' => $necessiteEquipement,
                ]);
                
                // Set prerequisite for next module (chain)
                if ($index < count($selectedModules) - 1) {
                    $preRequisId = $module->id_module;
                }
            }
        }
    }
}
