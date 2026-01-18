<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UsersMeta;
use App\Models\Departement;
use App\Models\Professeur;
use App\Models\Etudiant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMINS
        |--------------------------------------------------------------------------
        */
        $admin1 = User::updateOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password'),
            ]
        );

        UsersMeta::updateOrCreate(
            ['id' => $admin1->id],
            ['role' => 'admin_examens']
        );

        // Grant platform access
        $admin1->permissions = ['platform.index' => true];
        $admin1->save();

        $admin2 = User::updateOrCreate(
            ['email' => 'admin2@demo.test'],
            [
                'name' => 'Admin Secondaire',
                'password' => Hash::make('password'),
            ]
        );

        UsersMeta::updateOrCreate(
            ['id' => $admin2->id],
            ['role' => 'admin_examens']
        );

        // Grant platform access
        $admin2->permissions = ['platform.index' => true];
        $admin2->save();

        /*
        |--------------------------------------------------------------------------
        | DOYENS
        |--------------------------------------------------------------------------
        */
        $doyen1 = User::updateOrCreate(
            ['email' => 'doyen@demo.test'],
            [
                'name' => 'Doyen Principal',
                'password' => Hash::make('password'),
            ]
        );

        UsersMeta::updateOrCreate(
            ['id' => $doyen1->id],
            ['role' => 'doyen']
        );

        // Grant platform access
        $doyen1->permissions = ['platform.index' => true];
        $doyen1->save();

        $doyen2 = User::updateOrCreate(
            ['email' => 'doyen2@demo.test'],
            [
                'name' => 'Doyen Adjoint',
                'password' => Hash::make('password'),
            ]
        );

        UsersMeta::updateOrCreate(
            ['id' => $doyen2->id],
            ['role' => 'doyen']
        );

        // Grant platform access
        $doyen2->permissions = ['platform.index' => true];
        $doyen2->save();

        /*
        |--------------------------------------------------------------------------
        | CHEF DEPARTEMENTS
        |--------------------------------------------------------------------------
        */
        $departements = Departement::all();

        foreach ($departements as $index => $dept) {
            $user = User::updateOrCreate(
                ['email' => 'chef' . ($index + 1) . '@demo.test'],
                [
                    'name' => 'Chef ' . $dept->nom,
                    'password' => Hash::make('password'),
                ]
            );

            UsersMeta::updateOrCreate(
                ['id' => $user->id],
                [
                    'role' => 'chef_dept',
                    'dept_id' => $dept->id_dept,
                ]
            );

            // Grant platform access
            $user->permissions = ['platform.index' => true];
            $user->save();
        }

        /*
        |--------------------------------------------------------------------------
        | PROFESSEURS
        |--------------------------------------------------------------------------
        */
        $professeurs = Professeur::take(10)->get();

        foreach ($professeurs as $index => $prof) {
            $user = User::updateOrCreate(
                ['email' => 'prof' . ($index + 1) . '@demo.test'],
                [
                    'name' => $prof->prenom . ' ' . $prof->nom,
                    'password' => Hash::make('password'),
                ]
            );

            UsersMeta::updateOrCreate(
                ['id' => $user->id],
                [
                    'role' => 'prof',
                    'id_prof' => $prof->id_prof,
                ]
            );

            // Grant platform access
            $user->permissions = ['platform.index' => true];
            $user->save();
        }

        /*
        |--------------------------------------------------------------------------
        | ETUDIANTS
        |--------------------------------------------------------------------------
        */
        $etudiants = Etudiant::take(10)->get();

        foreach ($etudiants as $index => $etudiant) {
            $user = User::updateOrCreate(
                ['email' => 'etudiant' . ($index + 1) . '@demo.test'],
                [
                    'name' => $etudiant->prenom . ' ' . $etudiant->nom,
                    'password' => Hash::make('password'),
                ]
            );

            UsersMeta::updateOrCreate(
                ['id' => $user->id],
                [
                    'role' => 'etudiant',
                    'id_etudiant' => $etudiant->id_etudiant,
                    'formation_id' => $etudiant->id_formation,
                ]
            );

            // Grant platform access
            $user->permissions = ['platform.index' => true];
            $user->save();
        }

        $this->command->info('✅ UserSeeder executed safely (idempotent)');
    }
}
