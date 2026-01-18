<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\UsersMeta;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // Include modern theme CSS
        // This will be loaded via app.css imports
    }

    /**
     * Get current user's role from users_meta
     */
    protected function getUserRole(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        
        // Try to get from relationship first
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
     * Check if user is admin or doyen (should see all menus)
     */
    protected function shouldShowAllMenus(): bool
    {
        $role = $this->getUserRole();
        return in_array($role, ['admin_examens', 'doyen', 'chef_dept']);
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        $userRole = $this->getUserRole();
        $showAllMenus = $this->shouldShowAllMenus();
        
        // Base menu - always visible
        $menu = [];
        
        // Only show full menus for admin, doyen, and chef_dept
        if ($showAllMenus) {
            $menu[] = Menu::make('Référentiel')
                ->icon('bs.database')
                ->title('Référentiel')
                ->list([
                    Menu::make('Universités')
                        ->icon('bs.building')
                        ->route('platform.referentiel.universites'),
                    Menu::make('Facultés')
                        ->icon('bs.building')
                        ->route('platform.referentiel.facultes'),
                    Menu::make('Départements')
                        ->icon('bs.building')
                        ->route('platform.referentiel.departements'),
                    Menu::make('Formations')
                        ->icon('bs.book')
                        ->route('platform.referentiel.formations'),
                    Menu::make('Modules')
                        ->icon('bs.file-text')
                        ->route('platform.referentiel.modules'),
                ]);

            $menu[] = Menu::make('Examens')
                ->icon('bs.clipboard')
                ->title('Examens')
                ->list([
                    Menu::make('Étudiants')
                        ->icon('bs.people')
                        ->route('platform.examens.etudiants'),
                    Menu::make('Professeurs')
                        ->icon('bs.person')
                        ->route('platform.examens.professeurs'),
                    Menu::make('Salles')
                        ->icon('bs.house')
                        ->route('platform.examens.salles'),
                    Menu::make('Équipements')
                        ->icon('bs.gear')
                        ->route('platform.examens.equipements'),
                    Menu::make('Périodes d\'Examen')
                        ->icon('bs.calendar')
                        ->route('platform.examens.periodes'),
                  
                    Menu::make('Sections')
                        ->icon('bs.collection')
                        ->route('platform.examens.sections'),
                    Menu::make('Groupes')
                        ->icon('bs.people')
                        ->route('platform.examens.groupes'),
                ]);

            $menu[] = Menu::make('Planning')
                ->icon('bs.calendar3')
                ->title('Planning')
                ->list([
                    Menu::make('Planning Runs')
                        ->icon('bs.play-circle')
                        ->route('platform.planning.runs'),
                    Menu::make('Planning Items')
                        ->icon('bs.list-ul')
                        ->route('platform.planning.items'),
                  
                ]);

            $menu[] = Menu::make('Statistiques')
                ->icon('bs.bar-chart')
                ->title('Statistiques')
                ->route('platform.statistiques');

            // System menus only for admin
            if ($userRole === 'admin_examens') {
                $menu[] = Menu::make(__('Users'))
                    ->icon('bs.people')
                    ->route('platform.systems.users')
                    ->permission('platform.systems.users')
                    ->title(__('Access Controls'));

                $menu[] = Menu::make(__('Roles'))
                    ->icon('bs.shield')
                    ->route('platform.systems.roles')
                    ->permission('platform.systems.roles');
            }
        } else {
            // For prof and etudiant: only show Planning Items
            $menu[] = Menu::make('Planning des Examens')
                ->icon('bs.calendar3')
                ->title('Planning')
                ->route('platform.planning.items');
        }
        
        return $menu;
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
