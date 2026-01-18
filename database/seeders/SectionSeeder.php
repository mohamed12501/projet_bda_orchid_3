<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Formation;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $formations = Formation::all();
        
        foreach ($formations as $formation) {
            // Create 2-4 sections per formation
            $nbSections = rand(2, 4);
            
            // Use letters for sections (A, B, C, D)
            $sectionLetters = ['A', 'B', 'C', 'D'];
            
            for ($i = 0; $i < $nbSections; $i++) {
                Section::create([
                    'nom' => "Section {$sectionLetters[$i]}",
                    'id_formation' => $formation->id_formation,
                ]);
            }
        }
    }
}
