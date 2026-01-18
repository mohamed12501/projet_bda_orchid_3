<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningItem;
use App\Models\UsersMeta;
use App\Orchid\Layouts\Planning\PlanningItemFiltersLayout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class PlanningItemListScreen extends Screen
{
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
     * Check if user should see all items (admin/doyen/chef_dept)
     */
    protected function canSeeAllItems(): bool
    {
        $role = $this->getUserRole();
        return in_array($role, ['admin_examens', 'doyen', 'chef_dept']);
    }

    /**
     * Get current student's group ID if user is a student
     */
    protected function getStudentGroupId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $meta = $user->meta ?? UsersMeta::find($user->id);
        if (!$meta || $meta->role !== 'etudiant' || !$meta->id_etudiant) {
            return null;
        }

        // Get group_id from etudiant table
        $etudiant = \App\Models\Etudiant::find($meta->id_etudiant);
        return $etudiant?->group_id;
    }

    /**
     * Check if current user is a student
     */
    protected function isStudent(): bool
    {
        return $this->getUserRole() === 'etudiant';
    }

    public function query(Request $request): iterable
    {
        $query = PlanningItem::with([
            'module.formation',
            'groupe.section',
            'salle',
            'creneau',
            'run',
        ]);

        $userRole = $this->getUserRole();

        // For prof and etudiant: only show published planning items
        if (!$this->canSeeAllItems()) {
            $query->whereHas('run', function ($q) {
                $q->where('published', true);
            });
        }

        // For students: only show planning items for their own group
        if ($this->isStudent()) {
            $studentGroupId = $this->getStudentGroupId();
            if ($studentGroupId) {
                $query->where('group_id', $studentGroupId);
            } else {
                // If student has no group, show nothing
                $query->whereRaw('1 = 0');
            }
        }

        // Apply filters based on request parameters
        if ($request->filled('run_id')) {
            $query->where('run_id', $request->get('run_id'));
        }

        if ($request->filled('dept_id')) {
            $query->whereHas('module.formation', function ($q) use ($request) {
                $q->where('id_dept', $request->get('dept_id'));
            });
        }

        if ($request->filled('formation_id')) {
            $query->whereHas('module', function ($q) use ($request) {
                $q->where('id_formation', $request->get('formation_id'));
            });
        }

        return [
            'items' => $query->latest()->paginate(15),
            'canEdit' => $this->canSeeAllItems(),
        ];
    }

    public function name(): ?string
    {
        return 'Planning des examens';
    }

    public function description(): ?string
    {
        return 'Liste des examens planifiés';
    }

    public function layout(): iterable
    {
        return [
            PlanningItemFiltersLayout::class,

            Layout::table('items', [
                TD::make('module', 'Module')
                    ->render(fn (PlanningItem $item) =>
                        $item->module?->nom ?? '—'
                    )
                    ->sort(),

                TD::make('groupe', 'Groupe')
                    ->render(fn (PlanningItem $item) =>
                        $item->groupe?->nom ?? 'Tous'
                    )
                    ->sort(),

                TD::make('salle', 'Salle')
                    ->render(fn (PlanningItem $item) =>
                        $item->salle?->nom ?? '—'
                    )
                    ->sort(),

                TD::make('date', 'Date')
                    ->render(fn (PlanningItem $item) =>
                        optional($item->creneau?->date)->format('d/m/Y') ?? '—'
                    )
                    ->sort(),

                TD::make('heure_debut', 'Heure début')
                    ->render(fn (PlanningItem $item) =>
                        $item->creneau?->heure_debut ?? '—'
                    ),

                TD::make('heure_fin', 'Heure fin')
                    ->render(fn (PlanningItem $item) =>
                        $item->creneau?->heure_fin ?? '—'
                    ),

                TD::make('expected_students', 'Étudiants attendus')
                    ->render(fn (PlanningItem $item) =>
                        (int) ($item->expected_students ?? 0)
                    ),

                TD::make('surveillants', 'Surveillants')
                    ->render(fn (PlanningItem $item) =>
                        is_array($item->surveillants)
                            ? count($item->surveillants)
                            : 0
                    ),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->canSee($this->canSeeAllItems())
                    ->render(fn (PlanningItem $item) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cet élément de planification ?'))
                                ->method('remove', [
                                    'id' => $item->id,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        // Check permissions
        if (!$this->canSeeAllItems()) {
            Toast::error(__('Vous n\'avez pas la permission d\'effectuer cette action.'));
            return redirect()->route('platform.planning.items');
        }

        $item = PlanningItem::findOrFail($request->get('id'));
        $item->delete();
        Toast::info(__('Élément de planification supprimé.'));
        return redirect()->route('platform.planning.items');
    }
}
