<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Etudiant;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class EtudiantListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'etudiants' => Etudiant::with('formation')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Étudiants';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.etudiants.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('etudiants', [
                TD::make('id_etudiant', 'ID')
                    ->render(fn (Etudiant $e) => $e->id_etudiant)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Etudiant $e) => $e->nom)
                    ->sort()
                    ->filter(),
                TD::make('prenom', 'Prénom')
                    ->render(fn (Etudiant $e) => $e->prenom)
                    ->sort()
                    ->filter(),
                TD::make('email', 'Email')
                    ->render(fn (Etudiant $e) => $e->email)
                    ->sort()
                    ->filter(),
                TD::make('formation.nom', 'Formation')
                    ->render(fn (Etudiant $e) => $e->formation->nom ?? '—')
                    ->sort(),
                TD::make('promo', 'Promo')
                    ->render(fn (Etudiant $e) => $e->promo)
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Etudiant $e) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.etudiants.edit', $e->id_etudiant)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cet étudiant ?'))
                                ->method('remove', [
                                    'id' => $e->id_etudiant,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $etudiant = Etudiant::findOrFail($request->get('id'));
        $etudiant->delete();
        Toast::info(__('Étudiant supprimé.'));
        return redirect()->route('platform.examens.etudiants');
    }
}
