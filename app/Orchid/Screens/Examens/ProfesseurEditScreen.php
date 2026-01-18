<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Professeur;
use App\Models\Departement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class ProfesseurEditScreen extends Screen
{
    public $professeur;

    public function query(Professeur $professeur = null): iterable
    {
        $this->professeur = $professeur ?? new Professeur();
        return [
            'professeur' => $this->professeur,
            'departements' => Departement::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->professeur->exists ? 'Modifier Professeur' : 'Créer Professeur';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Enregistrer')->icon('bs.check-circle')->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('professeur.nom')->title('Nom')->required(),
                Input::make('professeur.prenom')->title('Prénom')->required(),
                Input::make('professeur.email')->title('Email')->type('email')->required(),
                Input::make('professeur.date_naissance')->title('Date de naissance')->type('date')->required(),
                Input::make('professeur.grade')->title('Grade'),
                Select::make('professeur.id_dept')
                    ->title('Département')
                    ->options(Departement::all()->pluck('nom', 'id_dept'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('professeur');
        if ($this->professeur->exists) {
            $this->professeur->update($data);
        } else {
            $this->professeur = Professeur::create($data);
        }
        Toast::info('Professeur enregistré.');
        return redirect()->route('platform.examens.professeurs');
    }
}
