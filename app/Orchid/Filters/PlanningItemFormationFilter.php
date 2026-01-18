<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use App\Models\Formation;
use Orchid\Screen\Fields\Select;

class PlanningItemFormationFilter extends Filter
{
    public function name(): string
    {
        return 'Formation';
    }

    public function parameters(): array
    {
        return ['formation_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->whereHas('module', function (Builder $query) {
            $query->where('formation_id', $this->request->get('formation_id'));
        });
    }

    public function display(): array
    {
        return [
            Select::make('formation_id')
                ->fromModel(Formation::class, 'nom', 'id_formation')
                ->empty('Toutes')
                ->value($this->request->get('formation_id'))
                ->title('Formation'),
        ];
    }

    public function value(): string
    {
        $formationId = $this->request->get('formation_id');
        if ($formationId) {
            $formation = Formation::find($formationId);
            return $formation ? $this->name() . ': ' . $formation->nom : '';
        }
        return '';
    }
}
