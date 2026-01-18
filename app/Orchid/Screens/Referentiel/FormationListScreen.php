<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Formation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FormationListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'formations' => Formation::with('departement')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Formations';
    }

    public function description(): ?string
    {
        return 'Gestion des formations';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')
                ->icon('bs.plus-circle')
                ->route('platform.referentiel.formations.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('formations', [
                TD::make('id_formation', 'ID')
                    ->render(fn (Formation $f) => $f->id_formation)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Formation $f) => $f->nom)
                    ->sort()
                    ->filter(),
                TD::make('niveau', 'Niveau')
                    ->render(fn (Formation $f) => $f->niveau ?? '—')
                    ->sort(),
                TD::make('nb_modules', 'Nb Modules')
                    ->render(fn (Formation $f) => $f->nb_modules ?? '—')
                    ->sort(),
                TD::make('departement.nom', 'Département')
                    ->render(fn (Formation $f) => $f->departement->nom ?? '—')
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Formation $f) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.referentiel.formations.edit', $f->id_formation)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette formation ?'))
                                ->method('remove', [
                                    'id' => $f->id_formation,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $formation = Formation::findOrFail($request->get('id'));
        $formation->delete();
        Toast::info(__('Formation supprimée.'));
        return redirect()->route('platform.referentiel.formations');
    }
}
