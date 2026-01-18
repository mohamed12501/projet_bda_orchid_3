<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningRun;
use App\Models\PlanningItem;
use App\Models\Departement;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\PeriodeExamen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PlanningRunEditScreen extends Screen
{
    public $run;
    protected $professeurMap;

    public function query(PlanningRun $run = null): iterable
    {
        $this->run = $run ?? new PlanningRun();
        
        $data = [
            'run' => $this->run,
            'departements' => Departement::all(),
            'formations' => Formation::all(),
            'periodes' => PeriodeExamen::all(),
        ];

        // If run exists, load detailed information
        if ($this->run->exists) {
            $items = PlanningItem::where('run_id', $this->run->id)
                ->with(['module.formation', 'groupe.section', 'salle', 'creneau', 'module.inscriptions.etudiant'])
                ->get();

            // Get unique salles
            $salles = $items->pluck('salle')->filter()->unique('id_salle')->values();
            
            // Get unique modules
            $modules = $items->pluck('module')->filter()->unique('id_module')->values();
            
            // Get all professors from surveillants - flatten in case of nested arrays
            $professeurIds = $items->pluck('surveillants')
                ->filter()
                ->flatten(PHP_INT_MAX) // Flatten all levels to handle nested arrays
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            
            $professeurs = !empty($professeurIds) 
                ? \App\Models\Professeur::whereIn('id_prof', $professeurIds)->get()
                : collect();
            
            // Create a lookup map for professors - ensure keys are strings for consistency
            $this->professeurMap = $professeurs->keyBy(function ($prof) {
                return (string) $prof->id_prof;
            });
            
            // Get all students from modules
            $moduleIds = $modules->pluck('id_module');
            $studentIds = Inscription::whereIn('id_module', $moduleIds)
                ->pluck('id_etudiant')
                ->unique()
                ->values();
            
            $students = \App\Models\Etudiant::whereIn('id_etudiant', $studentIds)
                ->with('formation')
                ->get();

            $data['planning_items'] = $items;
            $data['salles'] = $salles;
            $data['modules'] = $modules;
            $data['professeurs'] = $professeurs;
            $data['students'] = $students;
            $data['items_count'] = $items->count();
            $data['salles_count'] = $salles->count();
            $data['modules_count'] = $modules->count();
            $data['professeurs_count'] = $professeurs->count();
            $data['students_count'] = $students->count();
        }

        return $data;
    }

    public function name(): ?string
    {
        return $this->run->exists ? 'Modifier Run' : 'Nouveau Run';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Enregistrer')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        $layouts = [
            Layout::rows([
                Select::make('run.scope')
                    ->title('Portée')
                    ->options([
                        'global' => 'Global',
                        'departement' => 'Département',
                        'formation' => 'Formation',
                    ])
                    ->required()
                    ->help('Sélectionnez la portée du planning'),
                Select::make('run.dept_id')
                    ->title('Département')
                    ->options(Departement::all()->pluck('nom', 'id_dept'))
                    ->help('Requis si portée = Département ou Formation')
                    ->empty('Sélectionner un département'),
                Select::make('run.formation_id')
                    ->title('Formation')
                    ->options(Formation::all()->pluck('nom', 'id_formation'))
                    ->help('Requis si portée = Formation')
                    ->empty('Sélectionner une formation'),
                Select::make('run.periode_id')
                    ->title('Période d\'Examen')
                    ->options(PeriodeExamen::all()->pluck('nom', 'id_periode'))
                    ->required()
                    ->help('Sélectionnez la période d\'examen pour ce planning')
                    ->empty('Sélectionner une période'),
            ]),
        ];

        // If run exists, add detailed information tabs
        if ($this->run->exists) {
            $professeurMap = $this->professeurMap ?? collect();
            
            $layouts[] = Layout::metrics([
                'Éléments Planifiés' => 'items_count',
                'Salles Utilisées' => 'salles_count',
                'Modules' => 'modules_count',
                'Professeurs' => 'professeurs_count',
                'Étudiants' => 'students_count',
            ]);

            $layouts[] = Layout::tabs([
                'Planning Complet' => Layout::table('planning_items', [
                    TD::make('module', 'Module')
                        ->render(fn (PlanningItem $item) => $item->module?->nom ?? '—')
                        ->sort(),
                    TD::make('groupe', 'Groupe')
                        ->render(fn (PlanningItem $item) => $item->groupe?->nom ?? 'Tous')
                        ->sort(),
                    TD::make('salle', 'Salle')
                        ->render(fn (PlanningItem $item) => $item->salle?->nom ?? '—')
                        ->sort(),
                    TD::make('date', 'Date')
                        ->render(fn (PlanningItem $item) => optional($item->creneau?->date)->format('d/m/Y') ?? '—')
                        ->sort(),
                    TD::make('heure', 'Heure')
                        ->render(fn (PlanningItem $item) => 
                            ($item->creneau?->heure_debut ?? '—') . ' - ' . ($item->creneau?->heure_fin ?? '—')
                        ),
                    TD::make('expected_students', 'Étudiants attendus')
                        ->render(fn (PlanningItem $item) => (int) ($item->expected_students ?? 0)),
                    TD::make('surveillants', 'Surveillants')
                        ->render(function (PlanningItem $item) use ($professeurMap) {
                            if (!is_array($item->surveillants) || empty($item->surveillants)) {
                                return 'Aucun';
                            }
                            
                            // Flatten surveillants in case it's a nested array
                            $surveillantIds = collect($item->surveillants)->flatten()->filter()->unique();
                            
                            $profNames = $surveillantIds->map(function ($profId) use ($professeurMap) {
                                // Handle both scalar and array values
                                if (is_array($profId)) {
                                    // If profId is an array, try to extract an ID from it
                                    $profId = $profId['id'] ?? $profId['id_prof'] ?? null;
                                }
                                
                                if ($profId === null) {
                                    return null;
                                }
                                
                                // Ensure profId is a string to match the keyBy format
                                $prof = $professeurMap->get((string) $profId);
                                return $prof ? ($prof->nom . ' ' . $prof->prenom) : null;
                            })
                            ->filter()
                            ->values();
                            
                            return $profNames->isNotEmpty() ? $profNames->join(', ') : 'Aucun';
                        }),
                ]),
                'Salles' => Layout::table('salles', [
                    TD::make('nom', 'Nom')
                        ->render(fn ($salle) => $salle->nom ?? '—')
                        ->sort(),
                    TD::make('capacite_examen', 'Capacité Examen')
                        ->render(fn ($salle) => $salle->capacite_examen ?? $salle->capacite ?? '—')
                        ->sort(),
                    TD::make('type', 'Type')
                        ->render(fn ($salle) => ucfirst($salle->type ?? '—'))
                        ->sort(),
                    TD::make('batiment', 'Bâtiment')
                        ->render(fn ($salle) => $salle->batiment ?? '—')
                        ->sort(),
                ]),
                'Modules' => Layout::table('modules', [
                    TD::make('nom', 'Nom')
                        ->render(fn ($module) => $module->nom ?? '—')
                        ->sort(),
                    TD::make('credits', 'Crédits')
                        ->render(fn ($module) => $module->credits ?? '—')
                        ->sort(),
                    TD::make('formation', 'Formation')
                        ->render(fn ($module) => $module->formation?->nom ?? '—')
                        ->sort(),
                    TD::make('necessite_equipement', 'Équipement')
                        ->render(fn ($module) => $module->necessite_equipement ? 'Oui' : 'Non'),
                ]),
                'Professeurs' => Layout::table('professeurs', [
                    TD::make('nom', 'Nom')
                        ->render(fn ($prof) => $prof->nom ?? '—')
                        ->sort(),
                    TD::make('prenom', 'Prénom')
                        ->render(fn ($prof) => $prof->prenom ?? '—')
                        ->sort(),
                    TD::make('email', 'Email')
                        ->render(fn ($prof) => $prof->email ?? '—')
                        ->sort(),
                    TD::make('departement', 'Département')
                        ->render(fn ($prof) => $prof->departement?->nom ?? '—')
                        ->sort(),
                    TD::make('grade', 'Grade')
                        ->render(fn ($prof) => $prof->grade ?? '—')
                        ->sort(),
                ]),
                'Étudiants' => Layout::table('students', [
                    TD::make('id_etudiant', 'ID')
                        ->render(fn ($student) => $student->id_etudiant ?? '—')
                        ->sort(),
                    TD::make('nom', 'Nom')
                        ->render(fn ($student) => $student->nom ?? '—')
                        ->sort(),
                    TD::make('prenom', 'Prénom')
                        ->render(fn ($student) => $student->prenom ?? '—')
                        ->sort(),
                    TD::make('email', 'Email')
                        ->render(fn ($student) => $student->email ?? '—')
                        ->sort(),
                    TD::make('formation', 'Formation')
                        ->render(fn ($student) => $student->formation?->nom ?? '—')
                        ->sort(),
                    TD::make('promo', 'Promo')
                        ->render(fn ($student) => $student->promo ?? '—')
                        ->sort(),
                ]),
            ]);
        }

        return $layouts;
    }

    public function save(Request $request, PlanningRun $run = null)
    {
        $data = $request->get('run');
        $data['created_by'] = auth()->id();
        
        // Validate periode_id is required
        if (empty($data['periode_id'])) {
            Toast::error('La période d\'examen est requise.');
            return back();
        }
        
        // Validate based on scope
        if ($data['scope'] === 'departement' || $data['scope'] === 'formation') {
            if (empty($data['dept_id'])) {
                Toast::error('Le département est requis pour cette portée.');
                return back();
            }
        }
        
        if ($data['scope'] === 'formation') {
            if (empty($data['formation_id'])) {
                Toast::error('La formation est requise pour cette portée.');
                return back();
            }
        }
        
        if ($run && $run->exists) {
            $run->update($data);
        } else {
            // Set default metrics if not provided
            if (!isset($data['metrics'])) {
                $data['metrics'] = [];
            }
            $run = PlanningRun::create($data);
        }
        
        Toast::info('Run enregistré.');
        return redirect()->route('platform.planning.runs');
    }
}