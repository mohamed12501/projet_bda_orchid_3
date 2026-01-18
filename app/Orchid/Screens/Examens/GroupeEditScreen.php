<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Groupe;
use App\Models\Section;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class GroupeEditScreen extends Screen
{
    public $groupe;

    public function query(Groupe $groupe = null): iterable
    {
        $this->groupe = $groupe ?? new Groupe();
        return [
            'groupe' => $this->groupe,
            'sections' => Section::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->groupe->exists ? 'Modifier Groupe' : 'Créer Groupe';
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
                Input::make('groupe.nom')->title('Nom')->required(),
                Select::make('groupe.id_section')
                    ->title('Section')
                    ->options(Section::all()->pluck('nom', 'id_section'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('groupe');
        if ($this->groupe->exists) {
            $this->groupe->update($data);
        } else {
            $this->groupe = Groupe::create($data);
        }
        Toast::info('Groupe enregistré.');
        return redirect()->route('platform.examens.groupes');
    }
}
