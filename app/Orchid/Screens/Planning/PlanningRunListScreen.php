<?php

namespace App\Orchid\Screens\Planning;

use App\Models\PlanningRun;
use App\Models\PeriodeExamen;
use App\Models\UsersMeta;
use App\Services\PlanningOptimizerService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlanningRunListScreen extends Screen
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
        
        // Try to get from relationship first (if already loaded)
        if ($user->relationLoaded('meta')) {
            return $user->meta?->role;
        }
        
        // Try to access the relationship (will lazy load)
        if ($user->meta) {
            return $user->meta->role;
        }
        
        // Fallback to direct query
        $meta = UsersMeta::find($user->id);
        return $meta?->role;
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole(string $role): bool
    {
        $userRole = $this->getUserRole();
        return $userRole === $role;
    }

    /**
     * Check if user is admin or doyen
     */
    protected function isAdmin(): bool
    {
        return $this->hasRole('admin_examens');
    }

    protected function isDoyen(): bool
    {
        return $this->hasRole('doyen');
    }

    protected function isChefDept(): bool
    {
        return $this->hasRole('chef_dept');
    }
    public function query(): iterable
    {
        $user = auth()->user();
        
        // Eager load user meta for role checking
        if ($user) {
            $user->load('meta');
        }
        
        $query = PlanningRun::with(['creator', 'departement', 'formation'])
            ->latest();
        
        // Filter by role if needed
        if ($this->isChefDept()) {
            $userMeta = UsersMeta::find(auth()->id());
            if ($userMeta && $userMeta->dept_id) {
                $query->where('dept_id', $userMeta->dept_id);
            }
        }
        
        return [
            'runs' => $query->paginate(15),
            'periodes' => PeriodeExamen::all(),
        ];
    }

    public function name(): ?string
    {
        return 'Planning – Runs';
    }

    public function description(): ?string
    {
        return 'Gestion des cycles de planification';
    }

    public function commandBar(): iterable
    {
        $actions = [];
        
        // Only admin and chef_dept can create runs
        if ($this->isAdmin() || $this->isChefDept()) {
            $actions[] = Link::make('Nouveau Run')
                ->icon('bs.plus-circle')
                ->route('platform.planning.runs.create');
        }
        
        return $actions;
    }

    public function layout(): iterable
    {
        return [
            Layout::table('runs', [

                TD::make('id', 'ID')
                    ->render(fn (PlanningRun $run) =>
                        substr($run->id, 0, 8) . '…'
                    )
                    ->sort(),

                TD::make('scope', 'Portée')
                    ->render(fn (PlanningRun $run) =>
                        ucfirst($run->scope ?? '—')
                    ),

                TD::make('workflow', 'Étape')
                    ->render(fn (PlanningRun $run) => $this->getWorkflowStep($run)),

                TD::make('status', 'Statut Optimisation')
                    ->render(fn (PlanningRun $run) =>
                        match ($run->status) {
                            'pending' => $this->badge('En attente', 'secondary'),
                            'running' => $this->badge('En cours', 'info'),
                            'done'    => $this->badge('Terminé', 'success'),
                            'failed'  => $this->badge('Échoué', 'danger'),
                            default   => $this->badge($run->status ?? 'N/A', 'dark'),
                        }
                    ),

                TD::make('status_admin', 'Admin')
                    ->render(fn (PlanningRun $run) =>
                        match ($run->status_admin ?? 'draft') {
                            'draft'     => $this->badge('Brouillon', 'warning'),
                            'submitted' => $this->badge('Soumis', 'primary'),
                            default     => $this->badge($run->status_admin ?? 'draft', 'dark'),
                        }
                    ),

                TD::make('status_doyen', 'Doyen')
                    ->render(fn (PlanningRun $run) =>
                        match ($run->status_doyen ?? 'pending') {
                            'pending'  => $this->badge('En attente', 'secondary'),
                            'approved' => $this->badge('Approuvé', 'success'),
                            'rejected' => $this->badge('Rejeté', 'danger'),
                            default    => $this->badge($run->status_doyen ?? 'pending', 'dark'),
                        }
                    ),

                TD::make('published', 'Publié')
                    ->render(fn (PlanningRun $run) =>
                        ($run->published ?? false)
                            ? $this->badge('Oui', 'success')
                            : $this->badge('Non', 'secondary')
                    ),

                TD::make('created_at', 'Créé le')
                    ->render(fn (PlanningRun $run) =>
                        optional($run->created_at)->format('d/m/Y H:i')
                    )
                    ->sort(),

                TD::make('Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (PlanningRun $run) =>
                        DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list($this->getActionsForRun($run))
                    ),
            ]),
        ];
    }

    /* =======================================================
     | Helpers
     ======================================================= */
    private function badge(string $label, string $color): string
    {
        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * Get workflow step description
     */
    private function getWorkflowStep(PlanningRun $run): string
    {
        if ($run->published) {
            return $this->badge('Publié', 'success');
        }

        if ($run->status_doyen === 'approved') {
            return $this->badge('Approuvé - Prêt à publier', 'info');
        }

        if ($run->status_doyen === 'rejected') {
            return $this->badge('Rejeté', 'danger');
        }

        if ($run->status_admin === 'submitted') {
            return $this->badge('En attente validation Doyen', 'warning');
        }

        if ($run->status === 'done') {
            return $this->badge('Terminé - Prêt à soumettre', 'info');
        }

        if ($run->status === 'running') {
            return $this->badge('Optimisation en cours', 'info');
        }

        if ($run->status === 'failed') {
            return $this->badge('Échec - Relancer', 'danger');
        }

        return $this->badge('Brouillon - Lancer optimisation', 'secondary');
    }

    /**
     * Get available actions for a run based on role and workflow step
     */
    private function getActionsForRun(PlanningRun $run): array
    {
        $actions = [];
        $user = auth()->user();
        $userRole = $this->getUserRole();
        
        // More explicit role checks with fallback
        $isAdmin = $this->isAdmin() || ($userRole === 'admin_examens');
        $isDoyen = $this->isDoyen() || ($userRole === 'doyen');
        $isChefDept = $this->isChefDept() || ($userRole === 'chef_dept');

        // View details - available to all
        $actions[] = Link::make('Détails')
            ->icon('bs.eye')
            ->route('platform.planning.runs.show', $run->id);

        // STEP 1: Run Optimizer (Admin or Chef de Département)
        // Only show if status is pending, failed, or null
        $canRunOptimizer = ($isAdmin || $isChefDept) && 
            in_array($run->status ?? 'pending', ['pending', 'failed', null]);
        
        if ($canRunOptimizer) {
            // Get available periods for modal
            $periodes = PeriodeExamen::orderBy('date_debut', 'desc')->get();
            
            if ($periodes->isEmpty()) {
                $actions[] = Button::make('Lancer Optimiseur')
                    ->icon('bs.play')
                    ->disabled()
                    ->title('Aucune période d\'examen disponible');
            } else {
                $actions[] = Link::make('Lancer Optimiseur')
                    ->icon('bs.play')
                    ->route('platform.planning.runs.optimize', $run->id);
            }
        }

        // STEP 2: Submit to Dean (Admin only, after optimization is done)
        // Show if status is done and still in draft
        if ($isAdmin && 
            $run->status === 'done' && 
            ($run->status_admin ?? 'draft') === 'draft') {
            $actions[] = Button::make('Soumettre au Doyen')
                ->icon('bs.send')
                ->method('submitToDean', ['id' => $run->id])
                ->confirm('Soumettre ce planning au doyen pour validation ?');
        }

        // STEP 3: Approve/Reject (Doyen only, when submitted)
        // Show if submitted and pending approval
        if ($isDoyen && 
            ($run->status_admin ?? 'draft') === 'submitted' && 
            ($run->status_doyen ?? 'pending') === 'pending') {
            $actions[] = Button::make('Approuver')
                ->icon('bs.check')
                ->method('approve', ['id' => $run->id])
                ->confirm('Approuver ce planning ?');

            $actions[] = Button::make('Rejeter')
                ->icon('bs.x')
                ->method('reject', ['id' => $run->id])
                ->confirm('Rejeter ce planning ? Vous pourrez indiquer une raison.');
        }

        // STEP 4: Publish (Admin only, after approval)
        // Check if run is approved by doyen
        $statusDoyen = $run->status_doyen ?? 'pending';
        $isApproved = ($statusDoyen === 'approved');
        
        // Check if already published (handle different types from DB)
        $published = $run->getAttribute('published');
        if ($published === null || $published === false || $published === 0 || $published === '0') {
            $published = false;
        } else {
            $published = true;
        }
        $isNotPublished = !$published;
        
        // Show publish button for admin when approved and not published
        if ($isAdmin && $isApproved && $isNotPublished) {
            $actions[] = Button::make('Publier')
                ->icon('bs.globe')
                ->method('publish', ['id' => $run->id])
                ->confirm('Publier ce planning ? Il sera visible par tous.');
        }

        return $actions;
    }

    /* =======================================================
     | Actions
     ======================================================= */


    public function submitToDean(Request $request)
    {
        // Check permissions
        if (!$this->isAdmin()) {
            Toast::error('Seuls les administrateurs peuvent soumettre au doyen.');
            return;
        }

        $run = PlanningRun::findOrFail($request->id);

        // Check workflow step
        if ($run->status !== 'done' || ($run->status_admin ?? 'draft') !== 'draft') {
            Toast::error('Le planning doit être terminé et en brouillon pour être soumis.');
            return;
        }

        $run->update([
            'status_admin' => 'submitted',
            'submitted_at' => now(),
        ]);

        Toast::success('Planning soumis au doyen pour validation.');
    }

    public function approve(Request $request)
    {
        // Check permissions
        if (!$this->isDoyen()) {
            Toast::error('Seul le doyen peut approuver un planning.');
            return;
        }

        $run = PlanningRun::findOrFail($request->id);

        // Check workflow step
        if (($run->status_admin ?? 'draft') !== 'submitted' || 
            ($run->status_doyen ?? 'pending') !== 'pending') {
            Toast::error('Ce planning n\'est pas en attente de validation.');
            return;
        }

        $run->update([
            'status_doyen' => 'approved',
            'approved_at'  => now(),
            'approved_by'  => auth()->id(),
        ]);

        Toast::success('Planning approuvé. Il peut maintenant être publié.');
    }

    public function reject(Request $request)
    {
        // Check permissions
        if (!$this->isDoyen()) {
            Toast::error('Seul le doyen peut rejeter un planning.');
            return;
        }

        $run = PlanningRun::findOrFail($request->id);

        // Check workflow step
        if (($run->status_admin ?? 'draft') !== 'submitted' || 
            ($run->status_doyen ?? 'pending') !== 'pending') {
            Toast::error('Ce planning n\'est pas en attente de validation.');
            return;
        }

        $reason = $request->get('reason', 'Raison non spécifiée');

        $run->update([
            'status_doyen'     => 'rejected',
            'rejected_at'     => now(),
            'rejected_by'     => auth()->id(),
            'rejection_reason'=> $reason,
        ]);

        Toast::info('Planning rejeté. L\'administrateur peut le modifier et le resoumettre.');
    }

    public function publish(Request $request)
    {
        // Check permissions
        if (!$this->isAdmin()) {
            Toast::error('Seuls les administrateurs peuvent publier un planning.');
            return;
        }

        $run = PlanningRun::findOrFail($request->id);

        // Check workflow step
        if (($run->status_doyen ?? 'pending') !== 'approved') {
            Toast::error('Le planning doit être approuvé par le doyen avant publication.');
            return;
        }

        if ($run->published) {
            Toast::warning('Ce planning est déjà publié.');
            return;
        }

        $run->update([
            'published'    => true,
            'published_at' => now(),
        ]);

        Toast::success('Planning publié avec succès. Il est maintenant visible par tous.');
    }
}
