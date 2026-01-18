<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Equipement;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class EquipementEditScreen extends Screen
{
    public $equipement;

    public function query(Equipement $equipement = null): iterable
    {
        $this->equipement = $equipement ?? new Equipement();
        return [
            'equipement' => $this->equipement,
        ];
    }

    public function name(): ?string
    {
        return $this->equipement->exists ? 'Modifier Équipement' : 'Créer Équipement';
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
                Input::make('equipement.nom')->title('Nom')->required(),
                TextArea::make('equipement.description')->title('Description'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('equipement');
        if ($this->equipement->exists) {
            $this->equipement->update($data);
        } else {
            $this->equipement = Equipement::create($data);
        }
        Toast::info('Équipement enregistré.');
        return redirect()->route('platform.examens.equipements');
    }
}
