<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UniversiteSeeder::class,
            FaculteSeeder::class,
            DepartementSeeder::class,
            FormationSeeder::class,
            SectionSeeder::class,
            GroupeSeeder::class,
            ModuleSeeder::class,
            EtudiantSeeder::class,
            ProfesseurSeeder::class,
            SalleSeeder::class,
            EquipementSeeder::class,
            PeriodeExamenSeeder::class,
            InscriptionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
