<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Departement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class DepartementListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'departements' => Departement::with('faculte')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Départements';
    }

    public function description(): ?string
    {
        return 'Gestion des départements';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')
                ->icon('bs.plus-circle')
                ->route('platform.referentiel.departements.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('departements', [
                TD::make('id_dept', 'ID')
                    ->render(fn (Departement $d) => $d->id_dept)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Departement $d) => $d->nom)
                    ->sort()
                    ->filter(),
                TD::make('lieu', 'Lieu')
                    ->render(fn (Departement $d) => $d->lieu ?? '—')
                    ->sort()
                    ->filter(),
                TD::make('faculte.nom', 'Faculté')
                    ->render(fn (Departement $d) => $d->faculte->nom ?? '—')
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Departement $d) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.referentiel.departements.edit', $d->id_dept)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer ce département ?'))
                                ->method('remove', [
                                    'id' => $d->id_dept,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $departement = Departement::findOrFail($request->get('id'));
        $departement->delete();
        Toast::info(__('Département supprimé.'));
        return redirect()->route('platform.referentiel.departements');
    }
}
