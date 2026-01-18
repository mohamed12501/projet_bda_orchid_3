<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PlanningRun extends Model
{
    use HasContent;
    protected $table = 'planning_runs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'scope',
        'dept_id',
        'formation_id',
        'periode_id',
        'status',
        'published',
        'started_at',
        'ended_at',
        'metrics',
        'status_admin',
        'status_doyen',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metrics' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            // Set default metrics if not provided
            if (!isset($model->metrics) || $model->metrics === null) {
                $model->metrics = [];
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by', 'id');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'rejected_by', 'id');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'dept_id', 'id_dept');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id', 'id_formation');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeExamen::class, 'periode_id', 'id_periode');
    }

    public function planningItems(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'run_id', 'id');
    }
}
