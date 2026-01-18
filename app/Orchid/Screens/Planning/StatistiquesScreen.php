<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningRun;
use App\Models\Etudiant;
use App\Models\Professeur;
use App\Models\Module;
use App\Models\Salle;
use App\Services\ConflictService;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class StatistiquesScreen extends Screen
{
    public function __construct(protected ConflictService $conflictService)
    {
    }

    public function query(): iterable
    {
        $conflictSummary = $this->conflictService->getConflictSummary();

        return [
            'total_etudiants' => Etudiant::count(),
            'total_professeurs' => Professeur::count(),
            'total_modules' => Module::count(),
            'total_salles' => Salle::count(),
            'total_runs' => PlanningRun::count(),
            'runs_published' => PlanningRun::where('published', true)->count(),
            'conflict_summary' => $conflictSummary,
        ];
    }

    public function name(): ?string
    {
        return 'Statistiques';
    }

    public function description(): ?string
    {
        return 'Vue d\'ensemble des statistiques du système';
    }

    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Étudiants' => 'total_etudiants',
                'Total Professeurs' => 'total_professeurs',
                'Total Modules' => 'total_modules',
                'Total Salles' => 'total_salles',
                'Planning Runs' => 'total_runs',
                'Runs Publiés' => 'runs_published',
            ]),
            Layout::metrics([
                'Conflits Salle' => 'conflict_summary.room_conflicts_count',
                'Chevauchements Étudiants' => 'conflict_summary.student_overlaps_count',
                'Violations Max Examens/Jour' => 'conflict_summary.student_max_exams_violations',
                'Violations Max Surveillances/Jour' => 'conflict_summary.professor_max_surveillances_violations',
            ])->title('Conflits'),
        ];
    }
}
