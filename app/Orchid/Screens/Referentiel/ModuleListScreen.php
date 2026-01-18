<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Module;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ModuleListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'modules' => Module::with(['formation', 'preRequis'])->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Modules';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.referentiel.modules.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('modules', [
                TD::make('id_module', 'ID')
                    ->render(fn (Module $m) => $m->id_module)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Module $m) => $m->nom)
                    ->sort()
                    ->filter(),
                TD::make('credits', 'Crédits')
                    ->render(fn (Module $m) => $m->credits ?? '—')
                    ->sort(),
                TD::make('formation.nom', 'Formation')
                    ->render(fn (Module $m) => $m->formation->nom ?? '—')
                    ->sort(),
                TD::make('preRequis.nom', 'Prérequis')
                    ->render(fn (Module $m) => $m->preRequis->nom ?? 'Aucun'),
                TD::make('necessite_equipement', 'Équipement')
                    ->render(fn (Module $m) => $m->necessite_equipement ? 'Oui' : 'Non'),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Module $m) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.referentiel.modules.edit', $m->id_module)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer ce module ?'))
                                ->method('remove', [
                                    'id' => $m->id_module,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $module = Module::findOrFail($request->get('id'));
        $module->delete();
        Toast::info(__('Module supprimé.'));
        return redirect()->route('platform.referentiel.modules');
    }
}
