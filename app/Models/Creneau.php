<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Creneau extends Model
{
    use HasContent;
    protected $table = 'creneau';
    protected $primaryKey = 'id_creneau';
    
    // Disable updated_at since table only has created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'date',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function planningItems(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'creneau_id', 'id_creneau');
    }
}
