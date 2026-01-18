<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Inscription;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class InscriptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'inscriptions' => Inscription::with(['etudiant', 'module'])->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Inscriptions';
    }

    public function description(): ?string
    {
        return 'Liste des inscriptions étudiants-modules';
    }

    public function layout(): iterable
    {
        return [
            Layout::table('inscriptions', [
                TD::make('etudiant.nom', 'Étudiant')
                    ->render(fn (Inscription $i) => $i->etudiant->nom ?? '—')
                    ->sort(),
                TD::make('etudiant.prenom', 'Prénom')
                    ->render(fn (Inscription $i) => $i->etudiant->prenom ?? '—')
                    ->sort(),
                TD::make('module.nom', 'Module')
                    ->render(fn (Inscription $i) => $i->module->nom ?? '—')
                    ->sort(),
                TD::make('note', 'Note')
                    ->render(fn (Inscription $i) => $i->note !== null ? number_format($i->note, 2) : '—')
                    ->sort(),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Inscription $i) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette inscription ?'))
                                ->method('remove', [
                                    'etudiant_id' => $i->id_etudiant,
                                    'module_id' => $i->id_module,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove($etudiant_id, $module_id)
    {
        Inscription::where('id_etudiant', $etudiant_id)
            ->where('id_module', $module_id)
            ->delete();
        Toast::info(__('Inscription supprimée.'));
        return redirect()->route('platform.examens.inscriptions');
    }
}
