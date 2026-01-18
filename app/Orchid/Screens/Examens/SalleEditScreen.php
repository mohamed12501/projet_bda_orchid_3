<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Salle;
use App\Models\Equipement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class SalleEditScreen extends Screen
{
    public $salle;

    public function query(Salle $salle = null): iterable
    {
        $this->salle = $salle ?? new Salle();
        return [
            'salle' => $this->salle,
            'equipements' => Equipement::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->salle->exists ? 'Modifier Salle' : 'Créer Salle';
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
                Input::make('salle.nom')->title('Nom')->required(),
                Input::make('salle.capacite')->title('Capacité')->type('number'),
                Input::make('salle.capacite_examen')->title('Capacité Examen')->type('number'),
                Select::make('salle.type')
                    ->title('Type')
                    ->options([
                        'salle' => 'Salle',
                        'amphi' => 'Amphithéâtre',
                    ]),
                Input::make('salle.batiment')->title('Bâtiment')->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('salle');
        if ($this->salle->exists) {
            $this->salle->update($data);
        } else {
            $this->salle = Salle::create($data);
        }
        Toast::info('Salle enregistrée.');
        return redirect()->route('platform.examens.salles');
    }
}
