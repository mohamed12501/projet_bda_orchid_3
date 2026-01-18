<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Faculte;
use App\Models\Universite;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class FaculteEditScreen extends Screen
{
    public $faculte;

    public function query(Faculte $faculte = null): iterable
    {
        $this->faculte = $faculte ?? new Faculte();
        return [
            'faculte' => $this->faculte,
            'universites' => Universite::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->faculte->exists ? 'Modifier Faculté' : 'Créer Faculté';
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
                Input::make('faculte.nom')
                    ->title('Nom')
                    ->required(),
                Select::make('faculte.id_univ')
                    ->title('Université')
                    ->options(Universite::all()->pluck('nom', 'id_univ'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('faculte');
        if ($this->faculte->exists) {
            $this->faculte->update($data);
        } else {
            $this->faculte = Faculte::create($data);
        }
        Toast::info('Faculté enregistrée.');
        return redirect()->route('platform.referentiel.facultes');
    }
}
