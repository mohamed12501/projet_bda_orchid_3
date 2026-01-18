<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PlanningItem extends Model
{
    use HasContent;
    protected $table = 'planning_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'run_id',
        'module_id',
        'group_id',
        'salle_id',
        'creneau_id',
        'expected_students',
        'notes',
        'surveillants',
    ];

    protected $casts = [
        'expected_students' => 'integer',
        'surveillants' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PlanningRun::class, 'run_id', 'id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id_module');
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'salle_id', 'id_salle');
    }

    public function creneau(): BelongsTo
    {
        return $this->belongsTo(Creneau::class, 'creneau_id', 'id_creneau');
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'group_id', 'id_groupe');
    }
}
