<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Section;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SectionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'sections' => Section::with(['formation', 'groupes'])->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Sections';
    }

    public function description(): ?string
    {
        return 'Gestion des sections';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Ajouter')->icon('bs.plus-circle')->route('platform.examens.sections.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('sections', [
                TD::make('id_section', 'ID')
                    ->render(fn (Section $s) => $s->id_section)
                    ->sort(),
                TD::make('nom', 'Nom')
                    ->render(fn (Section $s) => $s->nom)
                    ->sort()
                    ->filter(),
                TD::make('formation', 'Formation')
                    ->render(fn (Section $s) => $s->formation?->nom ?? '—')
                    ->sort(),
                TD::make('groupes_count', 'Groupes')
                    ->render(fn (Section $s) => (string) $s->groupes->count()),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Section $s) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.examens.sections.edit', $s->id_section)
                                ->icon('bs.pencil'),
                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Voulez-vous vraiment supprimer cette section ?'))
                                ->method('remove', [
                                    'id' => $s->id_section,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request)
    {
        $section = Section::findOrFail($request->get('id'));
        $section->delete();
        Toast::info(__('Section supprimée.'));
        return redirect()->route('platform.examens.sections');
    }
}
