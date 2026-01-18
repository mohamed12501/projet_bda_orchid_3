<?php

namespace App\Orchid\Layouts\Planning;

use App\Orchid\Filters\PlanningItemRunFilter;
use App\Orchid\Filters\PlanningItemDepartementFilter;
use App\Orchid\Filters\PlanningItemFormationFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class PlanningItemFiltersLayout extends Selection
{
    public function filters(): array
    {
        return [
            PlanningItemRunFilter::class,
            PlanningItemDepartementFilter::class,
            PlanningItemFormationFilter::class,
        ];
    }
}
