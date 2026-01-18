<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Salle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SalleListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'salles' => Salle::with('equipements')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Salles';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.salles.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('salles', [
                TD::make('id_salle', 'ID')
                    ->render(fn (Salle $s) => $s->id_salle)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Salle $s) => $s->nom)
                    ->sort()
                    ->filter(),
                TD::make('capacite_examen', 'Capacité Examen')
                    ->render(fn (Salle $s) => $s->capacite_examen ?? $s->capacite ?? '—')
                    ->sort(),
                TD::make('type', 'Type')
                    ->render(fn (Salle $s) => ucfirst($s->type ?? '—'))
                    ->sort(),
                TD::make('batiment', 'Bâtiment')
                    ->render(fn (Salle $s) => $s->batiment)
                    ->sort(),
                TD::make('equipements', 'Équipements')
                    ->render(fn (Salle $s) => $s->equipements->pluck('nom')->join(', ') ?: 'Aucun'),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Salle $s) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.salles.edit', $s->id_salle)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette salle ?'))
                                ->method('remove', [
                                    'id' => $s->id_salle,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $salle = Salle::findOrFail($request->get('id'));
        $salle->delete();
        Toast::info(__('Salle supprimée.'));
        return redirect()->route('platform.examens.salles');
    }
}
