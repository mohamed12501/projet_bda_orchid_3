<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use App\Models\PlanningRun;
use Orchid\Screen\Fields\Select;

class PlanningItemRunFilter extends Filter
{
    public function name(): string
    {
        return 'Run';
    }

    public function parameters(): array
    {
        return ['run_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('run_id', $this->request->get('run_id'));
    }

    public function display(): array
    {
        return [
            Select::make('run_id')
                ->fromModel(PlanningRun::class, 'id', 'id')
                ->empty('Tous')
                ->value($this->request->get('run_id'))
                ->title('Run'),
        ];
    }

    public function value(): string
    {
        $runId = $this->request->get('run_id');
        if ($runId) {
            return $this->name() . ': ' . substr($runId, 0, 8);
        }
        return '';
    }
}
