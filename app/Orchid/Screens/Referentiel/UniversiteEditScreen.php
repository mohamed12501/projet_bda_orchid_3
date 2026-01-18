<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Universite;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class UniversiteEditScreen extends Screen
{
    public $universite;

    public function query(Universite $universite): iterable
    {
        return [
            'universite' => $universite,
        ];
    }

    public function name(): ?string
    {
        return $this->universite->exists ? 'Modifier Université' : 'Créer Université';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Enregistrer')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('universite.nom')
                    ->title('Nom')
                    ->required(),
                Input::make('universite.ville')
                    ->title('Ville')
                    ->required(),
            ]),
        ];
    }

    public function save(Universite $universite, Request $request)
    {
        $universite->fill($request->get('universite'))->save();
        Toast::info('Université enregistrée.');
        return redirect()->route('platform.referentiel.universites');
    }
}
