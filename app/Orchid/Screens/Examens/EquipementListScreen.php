<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Equipement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class EquipementListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'equipements' => Equipement::with('salles')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Équipements';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.equipements.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('equipements', [
                TD::make('id_equipement', 'ID')
                    ->render(fn (Equipement $e) => $e->id_equipement)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Equipement $e) => $e->nom)
                    ->sort()
                    ->filter(),
                TD::make('description', 'Description')
                    ->render(fn (Equipement $e) => $e->description ?? '—'),
                TD::make('salles', 'Salles')
                    ->render(fn (Equipement $e) => (string) $e->salles->count()),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Equipement $e) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.equipements.edit', $e->id_equipement)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cet équipement ?'))
                                ->method('remove', [
                                    'id' => $e->id_equipement,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $equipement = Equipement::findOrFail($request->get('id'));
        $equipement->delete();
        Toast::info(__('Équipement supprimé.'));
        return redirect()->route('platform.examens.equipements');
    }
}
