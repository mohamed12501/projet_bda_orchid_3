<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    use HasContent;
    protected $table = 'examen';
    protected $primaryKey = 'id_examen';
    public $timestamps = false;

    protected $fillable = [
        'id_module',
        'id_periode',
        'date_examen',
        'heure_debut',
        'duree_minutes',
        'statut',
    ];

    protected $casts = [
        'date_examen' => 'date',
        'heure_debut' => 'datetime:H:i',
        'duree_minutes' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'id_module', 'id_module');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeExamen::class, 'id_periode', 'id_periode');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SessionExamen::class, 'id_examen', 'id_examen');
    }

    public function professeurs(): BelongsToMany
    {
        return $this->belongsToMany(Professeur::class, 'surveillance', 'id_examen', 'id_prof')
            ->withPivot('role');
    }

    public function surveillances()
    {
        return $this->hasMany(Surveillance::class, 'id_examen', 'id_examen');
    }
}
