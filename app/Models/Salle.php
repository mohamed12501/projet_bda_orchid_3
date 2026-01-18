<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salle extends Model
{
    use HasContent;
    protected $table = 'salle';
    protected $primaryKey = 'id_salle';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'capacite',
        'type',
        'batiment',
        'capacite_normale',
        'capacite_examen',
    ];

    protected $casts = [
        'capacite' => 'integer',
        'capacite_normale' => 'integer',
        'capacite_examen' => 'integer',
    ];

    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'salle_equipement', 'id_salle', 'id_equipement')
            ->withPivot('quantite');
    }

    public function sessionsExamen(): HasMany
    {
        return $this->hasMany(SessionExamen::class, 'id_salle', 'id_salle');
    }

    public function planningItems(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'salle_id', 'id_salle');
    }
}
