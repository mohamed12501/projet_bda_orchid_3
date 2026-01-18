<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Faculte;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FaculteListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'facultes' => Faculte::with('universite')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Facultés';
    }

    public function description(): ?string
    {
        return 'Gestion des facultés';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')
                ->icon('bs.plus-circle')
                ->route('platform.referentiel.facultes.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('facultes', [
                TD::make('id_fac', 'ID')
                    ->render(fn (Faculte $f) => $f->id_fac)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Faculte $f) => $f->nom)
                    ->sort()
                    ->filter(),
                TD::make('universite.nom', 'Université')
                    ->render(fn (Faculte $f) => $f->universite->nom ?? '—')
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Faculte $f) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.referentiel.facultes.edit', $f->id_fac)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette faculté ?'))
                                ->method('remove', [
                                    'id' => $f->id_fac,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $faculte = Faculte::findOrFail($request->get('id'));
        $faculte->delete();
        Toast::info(__('Faculté supprimée.'));
        return redirect()->route('platform.referentiel.facultes');
    }
}
