<?php

namespace App\Orchid\Screens\Examens;

use App\Models\PeriodeExamen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PeriodeExamenListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'periodes' => PeriodeExamen::paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Périodes d\'Examen';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.periodes.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('periodes', [
                TD::make('id_periode', 'ID')
                    ->render(fn (PeriodeExamen $p) => $p->id_periode)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (PeriodeExamen $p) => $p->nom)
                    ->sort()
                    ->filter(),
                TD::make('date_debut', 'Date Début')
                    ->render(fn (PeriodeExamen $p) => $p->date_debut->format('d/m/Y'))
                    ->sort(),
                TD::make('date_fin', 'Date Fin')
                    ->render(fn (PeriodeExamen $p) => $p->date_fin->format('d/m/Y'))
                    ->sort(),
                TD::make('type', 'Type')
                    ->render(fn (PeriodeExamen $p) => ucfirst($p->type ?? '—'))
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (PeriodeExamen $p) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.periodes.edit', $p->id_periode)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette période ?'))
                                ->method('remove', [
                                    'id' => $p->id_periode,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $periode = PeriodeExamen::findOrFail($request->get('id'));
        $periode->delete();
        Toast::info(__('Période supprimée.'));
        return redirect()->route('platform.examens.periodes');
    }
}
