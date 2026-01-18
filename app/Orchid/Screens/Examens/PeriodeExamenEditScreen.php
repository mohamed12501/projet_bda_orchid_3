<?php

namespace App\Orchid\Screens\Examens;

use App\Models\PeriodeExamen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class PeriodeExamenEditScreen extends Screen
{
    public $periode;

    public function query(PeriodeExamen $periode = null): iterable
    {
        $this->periode = $periode ?? new PeriodeExamen();
        return [
            'periode' => $this->periode,
        ];
    }

    public function name(): ?string
    {
        return $this->periode->exists ? 'Modifier Période' : 'Créer Période';
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
                Input::make('periode.nom')->title('Nom')->required(),
                Input::make('periode.date_debut')->title('Date Début')->type('date')->required(),
                Input::make('periode.date_fin')->title('Date Fin')->type('date')->required(),
                Select::make('periode.type')
                    ->title('Type')
                    ->options([
                        'session1' => 'Session 1',
                        'session2' => 'Session 2',
                        'rattrapage' => 'Rattrapage',
                    ]),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('periode');
        if ($this->periode->exists) {
            $this->periode->update($data);
        } else {
            $this->periode = PeriodeExamen::create($data);
        }
        Toast::info('Période enregistrée.');
        return redirect()->route('platform.examens.periodes');
    }
}
