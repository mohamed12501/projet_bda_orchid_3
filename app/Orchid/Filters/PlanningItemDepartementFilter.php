<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use App\Models\Departement;
use Orchid\Screen\Fields\Select;

class PlanningItemDepartementFilter extends Filter
{
    public function name(): string
    {
        return 'Département';
    }

    public function parameters(): array
    {
        return ['dept_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->whereHas('module.formation', function (Builder $query) {
            $query->where('id_dept', $this->request->get('dept_id'));
        });
    }

    public function display(): array
    {
        return [
            Select::make('dept_id')
                ->fromModel(Departement::class, 'nom', 'id_dept')
                ->empty('Tous')
                ->value($this->request->get('dept_id'))
                ->title('Département'),
        ];
    }

    public function value(): string
    {
        $deptId = $this->request->get('dept_id');
        if ($deptId) {
            $dept = Departement::find($deptId);
            return $dept ? $this->name() . ': ' . $dept->nom : '';
        }
        return '';
    }
}
