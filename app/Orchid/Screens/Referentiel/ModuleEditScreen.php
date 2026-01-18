<?php

namespace App\Orchid\Screens\Referentiel;

use App\Models\Module;
use App\Models\Formation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class ModuleEditScreen extends Screen
{
    public $module;

    public function query(Module $module = null): iterable
    {
        return [
            'module' => $module ?? new Module(),
            'formations' => Formation::all(),
            'modules' => Module::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->module->exists ? 'Modifier Module' : 'Créer Module';
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
                Input::make('module.nom')->title('Nom')->required(),
                Input::make('module.credits')->title('Crédits')->type('number'),
                Select::make('module.id_formation')
                    ->title('Formation')
                    ->options(Formation::all()->pluck('nom', 'id_formation'))
                    ->required(),
                Select::make('module.pre_requis_id')
                    ->title('Prérequis')
                    ->options(Module::all()->pluck('nom', 'id_module'))
                    ->empty('Aucun'),
                CheckBox::make('module.necessite_equipement')
                    ->title('Nécessite équipement')
                    ->value(1),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('module');
        if ($this->module->exists) {
            $this->module->update($data);
        } else {
            $this->module = Module::create($data);
        }
        Toast::info('Module enregistré.');
        return redirect()->route('platform.referentiel.modules');
    }
}
