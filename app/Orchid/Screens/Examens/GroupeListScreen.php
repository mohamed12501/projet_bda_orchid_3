<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Groupe;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupeListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'groupes' => Groupe::with(['section.formation', 'etudiants'])->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Groupes';
    }

    public function description(): ?string
    {
        return 'Gestion des groupes';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.groupes.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('groupes', [
                TD::make('id_groupe', 'ID')
                    ->render(fn (Groupe $g) => $g->id_groupe)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Groupe $g) => $g->nom)
                    ->sort()
                    ->filter(),
                TD::make('section', 'Section')
                    ->render(fn (Groupe $g) => $g->section?->nom ?? '—')
                    ->sort(),
                TD::make('formation', 'Formation')
                    ->render(fn (Groupe $g) => $g->section?->formation?->nom ?? '—')
                    ->sort(),
                TD::make('etudiants_count', 'Étudiants')
                    ->render(fn (Groupe $g) => (string) $g->etudiants->count()),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Groupe $g) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.groupes.edit', $g->id_groupe)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer ce groupe ?'))
                                ->method('remove', [
                                    'id' => $g->id_groupe,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $groupe = Groupe::findOrFail($request->get('id'));
        $groupe->delete();
        Toast::info(__('Groupe supprimé.'));
        return redirect()->route('platform.examens.groupes');
    }
}
