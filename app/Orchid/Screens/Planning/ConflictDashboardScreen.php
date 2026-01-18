<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningRun;
use App\Services\ConflictService;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ConflictDashboardScreen extends Screen
{
    public function __construct(protected ConflictService $conflictService)
    {
    }

    public function query(): iterable
    {
        $runId = request()->query('run_id');
        $run = $runId ? PlanningRun::find($runId) : null;
        
        $conflicts = $this->conflictService->getAllConflicts($run);
        $summary = $this->conflictService->getConflictSummary($run);

        return [
            'summary' => $summary,
            'room_conflicts' => $conflicts['room_conflicts'],
            'student_overlaps' => $conflicts['student_overlaps'],
            'student_max_exams' => $conflicts['student_max_exams_per_day'],
            'professor_max_surveillances' => $conflicts['professor_max_surveillances_per_day'],
            'runs' => PlanningRun::all(),
        ];
    }

    public function name(): ?string
    {
        return 'Tableau de Bord des Conflits';
    }

    public function description(): ?string
    {
        return 'Détection et visualisation des conflits de planification';
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('run_id')
                    ->title('Run')
                    ->options(PlanningRun::all()->pluck('id', 'id'))
                    ->empty('Tous les runs'),
            ]),
            Layout::metrics([
                'Total Conflits' => 'summary.total_conflicts',
                'Conflits Salle' => 'summary.room_conflicts_count',
                'Chevauchements Étudiants' => 'summary.student_overlaps_count',
                'Violations Max Examens/Jour' => 'summary.student_max_exams_violations',
                'Violations Max Surveillances/Jour' => 'summary.professor_max_surveillances_violations',
            ]),
            Layout::tabs([
                'Conflits Salle' => Layout::table('room_conflicts', [
                    TD::make('salle_id', 'Salle')
                        ->render(fn ($conflict) => $conflict['items'][0]->salle->nom ?? 'N/A'),
                    TD::make('creneau_id', 'Créneau')
                        ->render(fn ($conflict) => $conflict['items'][0]->creneau->date->format('d/m/Y') . ' ' . $conflict['items'][0]->creneau->heure_debut),
                    TD::make('count', 'Nombre')
                        ->render(fn ($conflict) => $conflict['count'] ?? 0),
                    TD::make('modules', 'Modules')
                        ->render(fn ($conflict) => $conflict['items']->pluck('module.nom')->join(', ')),
                ]),
                'Chevauchements Étudiants' => Layout::table('student_overlaps', [
                    TD::make('creneau', 'Créneau')
                        ->render(fn ($conflict) => $conflict['item1']->creneau->date->format('d/m/Y') . ' ' . $conflict['item1']->creneau->heure_debut),
                    TD::make('module1', 'Module 1')
                        ->render(fn ($conflict) => $conflict['item1']->module->nom),
                    TD::make('module2', 'Module 2')
                        ->render(fn ($conflict) => $conflict['item2']->module->nom),
                    TD::make('shared_count', 'Étudiants en commun')
                        ->render(fn ($conflict) => $conflict['shared_count'] ?? 0),
                ]),
                'Max Examens/Jour' => Layout::table('student_max_exams', [
                    TD::make('student_id', 'Étudiant')
                        ->render(fn ($v) => \App\Models\Etudiant::find($v['student_id'])->nom ?? 'N/A'),
                    TD::make('date', 'Date')
                        ->render(fn ($v) => $v['date'] ?? 'N/A'),
                    TD::make('count', 'Nombre d\'examens')
                        ->render(fn ($v) => $v['count'] ?? 0),
                ]),
                'Max Surveillances/Jour' => Layout::table('professor_max_surveillances', [
                    TD::make('prof_id', 'Professeur')
                        ->render(fn ($v) => \App\Models\Professeur::find($v['prof_id'])->nom ?? 'N/A'),
                    TD::make('date', 'Date')
                        ->render(fn ($v) => $v['date'] ?? 'N/A'),
                    TD::make('count', 'Nombre de surveillances')
                        ->render(fn ($v) => $v['count'] ?? 0),
                ]),
            ]),
        ];
    }
}
