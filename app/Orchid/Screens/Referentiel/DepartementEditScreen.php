<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Departement;
use App\Models\Faculte;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class DepartementEditScreen extends Screen
{
    public $departement;

    public function query(Departement $departement = null): iterable
    {
        $this->departement = $departement ?? new Departement();
        return [
            'departement' => $this->departement,
            'facultes' => Faculte::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->departement->exists ? 'Modifier Département' : 'Créer Département';
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
                Input::make('departement.nom')
                    ->title('Nom')
                    ->required(),
                Input::make('departement.lieu')
                    ->title('Lieu'),
                Select::make('departement.id_fac')
                    ->title('Faculté')
                    ->options(Faculte::all()->pluck('nom', 'id_fac'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('departement');
        if ($this->departement->exists) {
            $this->departement->update($data);
        } else {
            $this->departement = Departement::create($data);
        }
        Toast::info('Département enregistré.');
        return redirect()->route('platform.referentiel.departements');
    }
}
