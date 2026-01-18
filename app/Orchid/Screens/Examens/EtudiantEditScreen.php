<?php

namespace App\Orchid\Screens\Examens;

use App\Models\Etudiant;
use App\Models\Formation;
use App\Models\Section;
use App\Models\Groupe;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class EtudiantEditScreen extends Screen
{
    public $etudiant;

    public function query(Etudiant $etudiant = null): iterable
    {
        $this->etudiant = $etudiant ?? new Etudiant();
        
        return [
            'etudiant' => $this->etudiant,
        ];
    }

    public function name(): ?string
    {
        return $this->etudiant->exists ? 'Modifier Étudiant' : 'Créer Étudiant';
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
                Input::make('etudiant.nom')->title('Nom')->required(),
                Input::make('etudiant.prenom')->title('Prénom')->required(),
                Input::make('etudiant.email')->title('Email')->type('email')->required(),
                Input::make('etudiant.date_naissance')->title('Date de naissance')->type('date')->required(),
                Input::make('etudiant.promo')->title('Promo')->type('number')->required(),
                
                Select::make('etudiant.id_formation')
                    ->title('Formation')
                    ->options(Formation::all()->pluck('nom', 'id_formation')->toArray())
                    ->required()
                    ->help('Sélectionnez la formation'),
                
                Select::make('etudiant.section_id')
                    ->title('Section')
                    ->options([]) // Will be populated by JavaScript
                    ->empty('Sélectionner une section')
                    ->help('Sélectionnez d\'abord une formation'),
                
                Select::make('etudiant.group_id')
                    ->title('Groupe')
                    ->options([]) // Will be populated by JavaScript
                    ->empty('Sélectionner un groupe')
                    ->help('Sélectionnez d\'abord une section'),
            ]),
            
            // Add the JavaScript view
            Layout::view('orchid.etudiant-cascade'),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('etudiant');
        
        // Validate that section belongs to the selected formation
        if (isset($data['section_id']) && isset($data['id_formation'])) {
            $section = Section::find($data['section_id']);
            if ($section && $section->id_formation != $data['id_formation']) {
                Toast::error('La section sélectionnée n\'appartient pas à la formation sélectionnée.');
                return back();
            }
        }
        
        // Validate that group belongs to the selected section
        if (isset($data['group_id']) && isset($data['section_id'])) {
            $groupe = Groupe::find($data['group_id']);
            if ($groupe && $groupe->id_section != $data['section_id']) {
                Toast::error('Le groupe sélectionné n\'appartient pas à la section sélectionnée.');
                return back();
            }
        }
        
        if ($this->etudiant->exists) {
            $this->etudiant->update($data);
        } else {
            $this->etudiant = Etudiant::create($data);
        }
        
        Toast::info('Étudiant enregistré.');
        return redirect()->route('platform.examens.etudiants');
    }
}