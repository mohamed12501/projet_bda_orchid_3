<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Formation;
use App\Models\Departement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class FormationEditScreen extends Screen
{
    public $formation;

    public function query(Formation $formation = null): iterable
    {
        $this->formation = $formation ?? new Formation();
        return [
            'formation' => $this->formation,
            'departements' => Departement::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->formation->exists ? 'Modifier Formation' : 'Créer Formation';
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
                Input::make('formation.nom')
                    ->title('Nom')
                    ->required(),
                Select::make('formation.niveau')
                    ->title('Niveau')
                    ->options([
                        'L' => 'Licence',
                        'M' => 'Master',
                        'D' => 'Doctorat',
                    ]),
                Input::make('formation.nb_modules')
                    ->title('Nombre de modules')
                    ->type('number')
                    ->help('Entre 6 et 9'),
                Select::make('formation.id_dept')
                    ->title('Département')
                    ->options(Departement::all()->pluck('nom', 'id_dept'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('formation');
        if ($this->formation->exists) {
            $this->formation->update($data);
        } else {
            $this->formation = Formation::create($data);
        }
        Toast::info('Formation enregistrée.');
        return redirect()->route('platform.referentiel.formations');
    }
}
