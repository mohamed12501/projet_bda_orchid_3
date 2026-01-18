<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Professeur;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfesseurListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'professeurs' => Professeur::with('departement')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Professeurs';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.professeurs.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('professeurs', [
                TD::make('id_prof', 'ID')
                    ->render(fn (Professeur $p) => $p->id_prof)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Professeur $p) => $p->nom)
                    ->sort()
                    ->filter(),
                TD::make('prenom', 'Prénom')
                    ->render(fn (Professeur $p) => $p->prenom)
                    ->sort()
                    ->filter(),
                TD::make('email', 'Email')
                    ->render(fn (Professeur $p) => $p->email)
                    ->sort()
                    ->filter(),
                TD::make('departement.nom', 'Département')
                    ->render(fn (Professeur $p) => $p->departement->nom ?? '—')
                    ->sort(),
                TD::make('grade', 'Grade')
                    ->render(fn (Professeur $p) => $p->grade ?? '—')
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Professeur $p) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.professeurs.edit', $p->id_prof)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer ce professeur ?'))
                                ->method('remove', [
                                    'id' => $p->id_prof,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $professeur = Professeur::findOrFail($request->get('id'));
        $professeur->delete();
        Toast::info(__('Professeur supprimé.'));
        return redirect()->route('platform.examens.professeurs');
    }
}
