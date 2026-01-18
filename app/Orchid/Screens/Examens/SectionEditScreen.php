<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Section;
use App\Models\Formation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class SectionEditScreen extends Screen
{
    public $section;

    public function query(Section $section = null): iterable
    {
        $this->section = $section ?? new Section();
        return [
            'section' => $this->section,
            'formations' => Formation::all(),
        ];
    }

    public function name(): ?string
    {
        return $this->section->exists ? 'Modifier Section' : 'Créer Section';
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
                Input::make('section.nom')->title('Nom')->required(),
                Select::make('section.id_formation')
                    ->title('Formation')
                    ->options(Formation::all()->pluck('nom', 'id_formation'))
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('section');
        if ($this->section->exists) {
            $this->section->update($data);
        } else {
            $this->section = Section::create($data);
        }
        Toast::info('Section enregistrée.');
        return redirect()->route('platform.examens.sections');
    }
}
