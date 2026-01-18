<?php

namespace Database\Seeders;

use App\Models\Groupe;
use App\Models\Section;
use Illuminate\Database\Seeder;

class GroupeSeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::all();
        
        foreach ($sections as $section) {
            // Create 2-3 groups per section
            $nbGroupes = rand(2, 3);
            
            for ($i = 1; $i <= $nbGroupes; $i++) {
                Groupe::create([
                    'nom' => "{$section->nom} - Groupe {$i}",
                    'id_section' => $section->id_section,
                ]);
            }
        }
    }
}
