<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Universite;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UniversiteListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'universites' => Universite::with('facultes')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Universités';
    }

    public function description(): ?string
    {
        return 'Gestion des universités';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')
                ->icon('bs.plus-circle')
                ->route('platform.referentiel.universites.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('universites', [
                TD::make('id_univ', 'ID')
                    ->render(fn (Universite $u) => $u->id_univ)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Universite $u) => $u->nom)
                    ->sort()
                    ->filter(),
                TD::make('ville', 'Ville')
                    ->render(fn (Universite $u) => $u->ville)
                    ->sort()
                    ->filter(),
                TD::make('facultes_count', 'Facultés')
                    ->render(fn (Universite $u) => (string) $u->facultes->count()),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Universite $u) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.referentiel.universites.edit', $u->id_univ)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette université ?'))
                                ->method('remove', [
                                    'id' => $u->id_univ,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $universite = Universite::findOrFail($request->get('id'));
        $universite->delete();
        Toast::info(__('Université supprimée.'));
        return redirect()->route('platform.referentiel.universites');
    }
}
