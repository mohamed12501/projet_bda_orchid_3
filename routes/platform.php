<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\Referentiel\UniversiteListScreen;
use App\Orchid\Screens\Referentiel\UniversiteEditScreen;
use App\Orchid\Screens\Referentiel\FaculteListScreen;
use App\Orchid\Screens\Referentiel\FaculteEditScreen;
use App\Orchid\Screens\Referentiel\DepartementListScreen;
use App\Orchid\Screens\Referentiel\DepartementEditScreen;
use App\Orchid\Screens\Referentiel\FormationListScreen;
use App\Orchid\Screens\Referentiel\FormationEditScreen;
use App\Orchid\Screens\Referentiel\ModuleListScreen;
use App\Orchid\Screens\Referentiel\ModuleEditScreen;
use App\Orchid\Screens\Examens\EtudiantListScreen;
use App\Orchid\Screens\Examens\EtudiantEditScreen;
use App\Orchid\Screens\Examens\ProfesseurListScreen;
use App\Orchid\Screens\Examens\ProfesseurEditScreen;
use App\Orchid\Screens\Examens\SalleListScreen;
use App\Orchid\Screens\Examens\SalleEditScreen;
use App\Orchid\Screens\Examens\EquipementListScreen;
use App\Orchid\Screens\Examens\EquipementEditScreen;
use App\Orchid\Screens\Examens\PeriodeExamenListScreen;
use App\Orchid\Screens\Examens\PeriodeExamenEditScreen;
use App\Orchid\Screens\Examens\InscriptionListScreen;
use App\Orchid\Screens\Examens\SectionListScreen;
use App\Orchid\Screens\Examens\SectionEditScreen;
use App\Orchid\Screens\Examens\GroupeListScreen;
use App\Orchid\Screens\Examens\GroupeEditScreen;
use App\Orchid\Screens\Planning\PlanningRunListScreen;
use App\Orchid\Screens\Planning\PlanningRunEditScreen;
use App\Orchid\Screens\Planning\PlanningRunOptimizeScreen;
use App\Orchid\Screens\Planning\PlanningItemListScreen;
use App\Orchid\Screens\Planning\ConflictDashboardScreen;
use App\Orchid\Screens\Planning\StatistiquesScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Référentiel
Route::screen('referentiel/universites', UniversiteListScreen::class)->name('platform.referentiel.universites');
Route::screen('referentiel/universites/create', UniversiteEditScreen::class)->name('platform.referentiel.universites.create');
Route::screen('referentiel/universites/{universite}/edit', UniversiteEditScreen::class)->name('platform.referentiel.universites.edit');
Route::screen('referentiel/facultes', FaculteListScreen::class)->name('platform.referentiel.facultes');
Route::screen('referentiel/facultes/create', FaculteEditScreen::class)->name('platform.referentiel.facultes.create');
Route::screen('referentiel/facultes/{faculte}/edit', FaculteEditScreen::class)->name('platform.referentiel.facultes.edit');
Route::screen('referentiel/departements', DepartementListScreen::class)->name('platform.referentiel.departements');
Route::screen('referentiel/departements/create', DepartementEditScreen::class)->name('platform.referentiel.departements.create');
Route::screen('referentiel/departements/{departement}/edit', DepartementEditScreen::class)->name('platform.referentiel.departements.edit');
Route::screen('referentiel/formations', FormationListScreen::class)->name('platform.referentiel.formations');
Route::screen('referentiel/formations/create', FormationEditScreen::class)->name('platform.referentiel.formations.create');
Route::screen('referentiel/formations/{formation}/edit', FormationEditScreen::class)->name('platform.referentiel.formations.edit');
Route::screen('referentiel/modules', ModuleListScreen::class)->name('platform.referentiel.modules');
Route::screen('referentiel/modules/create', ModuleEditScreen::class)->name('platform.referentiel.modules.create');
Route::screen('referentiel/modules/{module}/edit', ModuleEditScreen::class)->name('platform.referentiel.modules.edit');

// Examens
Route::screen('examens/etudiants', EtudiantListScreen::class)->name('platform.examens.etudiants');
Route::screen('examens/etudiants/create', EtudiantEditScreen::class)->name('platform.examens.etudiants.create');
Route::screen('examens/etudiants/{etudiant}/edit', EtudiantEditScreen::class)->name('platform.examens.etudiants.edit');
Route::screen('examens/professeurs', ProfesseurListScreen::class)->name('platform.examens.professeurs');
Route::screen('examens/professeurs/create', ProfesseurEditScreen::class)->name('platform.examens.professeurs.create');
Route::screen('examens/professeurs/{professeur}/edit', ProfesseurEditScreen::class)->name('platform.examens.professeurs.edit');
Route::screen('examens/salles', SalleListScreen::class)->name('platform.examens.salles');
Route::screen('examens/salles/create', SalleEditScreen::class)->name('platform.examens.salles.create');
Route::screen('examens/salles/{salle}/edit', SalleEditScreen::class)->name('platform.examens.salles.edit');
Route::screen('examens/equipements', EquipementListScreen::class)->name('platform.examens.equipements');
Route::screen('examens/equipements/create', EquipementEditScreen::class)->name('platform.examens.equipements.create');
Route::screen('examens/equipements/{equipement}/edit', EquipementEditScreen::class)->name('platform.examens.equipements.edit');
Route::screen('examens/periodes', PeriodeExamenListScreen::class)->name('platform.examens.periodes');
Route::screen('examens/periodes/create', PeriodeExamenEditScreen::class)->name('platform.examens.periodes.create');
Route::screen('examens/periodes/{periode}/edit', PeriodeExamenEditScreen::class)->name('platform.examens.periodes.edit');
Route::screen('examens/inscriptions', InscriptionListScreen::class)->name('platform.examens.inscriptions');
Route::screen('examens/sections', SectionListScreen::class)->name('platform.examens.sections');
Route::screen('examens/sections/create', SectionEditScreen::class)->name('platform.examens.sections.create');
Route::screen('examens/sections/{section}/edit', SectionEditScreen::class)->name('platform.examens.sections.edit');
Route::screen('examens/groupes', GroupeListScreen::class)->name('platform.examens.groupes');
Route::screen('examens/groupes/create', GroupeEditScreen::class)->name('platform.examens.groupes.create');
Route::screen('examens/groupes/{groupe}/edit', GroupeEditScreen::class)->name('platform.examens.groupes.edit');

// Planning
Route::screen('planning/runs', PlanningRunListScreen::class)->name('platform.planning.runs');
Route::screen('planning/runs/create', PlanningRunEditScreen::class)->name('platform.planning.runs.create');
Route::screen('planning/runs/{run}/optimize', PlanningRunOptimizeScreen::class)->name('platform.planning.runs.optimize');
Route::screen('planning/runs/{run}', PlanningRunEditScreen::class)->name('platform.planning.runs.show');
Route::screen('planning/items', PlanningItemListScreen::class)->name('platform.planning.items');
Route::screen('planning/conflicts', ConflictDashboardScreen::class)->name('platform.planning.conflicts');
Route::screen('statistiques', StatistiquesScreen::class)->name('platform.statistiques');

// Route::screen('idea', Idea::class, 'platform.screens.idea');
