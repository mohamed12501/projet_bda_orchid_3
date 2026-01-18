<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningRun;
use App\Models\PeriodeExamen;
use App\Models\UsersMeta;
use App\Services\PlanningOptimizerService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlanningRunOptimizeScreen extends Screen
{
    public $run;

    /**
     * Get current user's role from users_meta
     */
    protected function getUserRole(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        
        if ($user->relationLoaded('meta')) {
            return $user->meta?->role;
        }
        
        if ($user->meta) {
            return $user->meta->role;
        }
        
        $meta = UsersMeta::find($user->id);
        return $meta?->role;
    }

    /**
     * Check if user is admin or chef_dept
     */
    protected function isAdmin(): bool
    {
        return $this->getUserRole() === 'admin_examens';
    }

    protected function isChefDept(): bool
    {
        return $this->getUserRole() === 'chef_dept';
    }

    public function query(PlanningRun $run): iterable
    {
        // Check permissions
        if (!$this->isAdmin() && !$this->isChefDept()) {
            abort(403, 'Vous n\'avez pas la permission d\'effectuer cette action.');
        }

        // Check workflow step
        $status = $run->status ?? 'pending';
        if (!in_array($status, ['pending', 'failed', null])) {
            abort(403, 'Cette action n\'est pas disponible à cette étape du workflow.');
        }

        return [
            'run' => $run,
            'periodes' => PeriodeExamen::orderBy('date_debut', 'desc')->get(),
        ];
    }

    public function name(): ?string
    {
        return 'Lancer l\'Optimisation';
    }

    public function description(): ?string
    {
        return 'Sélectionnez une période d\'examen pour générer le planning optimisé';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Retour')
                ->icon('bs.arrow-left')
                ->method('back'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('periode_id')
                    ->title('Période d\'Examen')
                    ->help('Sélectionnez la période d\'examen pour laquelle générer le planning')
                    ->options(function () {
                        return PeriodeExamen::orderBy('date_debut', 'desc')
                            ->get()
                            ->mapWithKeys(function ($periode) {
                                $label = sprintf(
                                    '%s - Du %s au %s (%d jours ouvrables)',
                                    $periode->nom ?? 'Période sans nom',
                                    $periode->date_debut ? \Carbon\Carbon::parse($periode->date_debut)->format('d/m/Y') : 'N/A',
                                    $periode->date_fin ? \Carbon\Carbon::parse($periode->date_fin)->format('d/m/Y') : 'N/A',
                                    $this->countWorkingDays($periode->date_debut, $periode->date_fin)
                                );
                                return [$periode->id_periode => $label];
                            })
                            ->toArray();
                    })
                    ->empty('Sélectionnez une période', 0)
                    ->required(),
            ]),

            Layout::rows([
                Button::make('Lancer l\'Optimisation')
                    ->icon('bs.play-fill')
                    ->method('runOptimizer')
                    ->confirm('Êtes-vous sûr de vouloir lancer l\'optimisation ? Cette opération peut prendre quelques minutes.'),
            ]),
        ];
    }

    /**
     * Count working days (excluding weekends) between two dates
     */
    private function countWorkingDays(?string $start, ?string $end): int
    {
        if (!$start || !$end) {
            return 0;
        }

        try {
            $startDate = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);
            $count = 0;

            while ($startDate->lte($endDate)) {
                if ($startDate->dayOfWeek !== \Carbon\Carbon::SATURDAY && 
                    $startDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                    $count++;
                }
                $startDate->addDay();
            }

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function runOptimizer(Request $request, PlanningOptimizerService $optimizer)
    {
        // Validate input
        $request->validate([
            'periode_id' => 'required|exists:periode_examen,id_periode',
        ]);

        $run = $this->run;
        $periodeId = $request->get('periode_id');

        // Check permissions
        if (!$this->isAdmin() && !$this->isChefDept()) {
            Toast::error('Vous n\'avez pas la permission d\'effectuer cette action.');
            return redirect()->route('platform.planning.runs');
        }

        // Check workflow step
        $status = $run->status ?? 'pending';
        if (!in_array($status, ['pending', 'failed', null])) {
            Toast::error('Cette action n\'est pas disponible à cette étape.');
            return redirect()->route('platform.planning.runs');
        }

        try {
            $result = $optimizer->optimize($run, $periodeId);
            
            $message = sprintf(
                'Optimisation terminée: %d éléments planifiés, %d conflits.',
                $result['scheduled'] ?? 0,
                $result['conflicts'] ?? 0
            );
            
            if (($result['scheduled'] ?? 0) === 0) {
                Toast::warning($message . ' Vérifiez les données (modules, groupes, salles, inscriptions).');
            } else {
                Toast::success($message);
            }

            return redirect()->route('platform.planning.runs');
        } catch (\Throwable $e) {
            \Log::error('Optimization error', [
                'run_id' => $run->id,
                'periode_id' => $periodeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Toast::error('Erreur lors de l\'optimisation: ' . $e->getMessage());
            return back();
        }
    }

    public function back()
    {
        return redirect()->route('platform.planning.runs');
    }
}
