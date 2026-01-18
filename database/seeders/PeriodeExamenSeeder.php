<?php

namespace Database\Seeders;

use App\Models\PeriodeExamen;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PeriodeExamenSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        PeriodeExamen::create([
            'nom' => 'Session 1 - Janvier 2024',
            'date_debut' => $now->copy()->addMonths(1)->startOfMonth(),
            'date_fin' => $now->copy()->addMonths(1)->startOfMonth()->addDays(14),
            'type' => 'session1',
        ]);
        
        PeriodeExamen::create([
            'nom' => 'Session 2 - Juin 2024',
            'date_debut' => $now->copy()->addMonths(6)->startOfMonth(),
            'date_fin' => $now->copy()->addMonths(6)->startOfMonth()->addDays(14),
            'type' => 'session2',
        ]);
        
        PeriodeExamen::create([
            'nom' => 'Rattrapage - Septembre 2024',
            'date_debut' => $now->copy()->addMonths(9)->startOfMonth(),
            'date_fin' => $now->copy()->addMonths(9)->startOfMonth()->addDays(7),
            'type' => 'rattrapage',
        ]);
    }
}
