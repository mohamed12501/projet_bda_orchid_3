<?php

namespace Database\Seeders;

use App\Models\Inscription;
use App\Models\Etudiant;
use App\Models\Module;
use Illuminate\Database\Seeder;

class InscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $etudiants = Etudiant::all();
        
        foreach ($etudiants as $etudiant) {
            // Get modules from student's formation
            $modules = Module::where('id_formation', $etudiant->id_formation)->get();
            
            // Enroll student in 70-100% of modules
            $nbModules = (int)($modules->count() * rand(70, 100) / 100);
            $selectedModules = $modules->random($nbModules);
            
            foreach ($selectedModules as $module) {
                Inscription::create([
                    'id_etudiant' => $etudiant->id_etudiant,
                    'id_module' => $module->id_module,
                    'note' => rand(0, 100) > 20 ? rand(8, 20) : null, // 80% have grades
                ]);
            }
        }
    }
}
