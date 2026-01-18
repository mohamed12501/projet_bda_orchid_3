<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipement extends Model
{
    use HasContent;
    protected $table = 'equipement';
    protected $primaryKey = 'id_equipement';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'description',
    ];

    public function salles(): BelongsToMany
    {
        return $this->belongsToMany(Salle::class, 'salle_equipement', 'id_equipement', 'id_salle')
            ->withPivot('quantite');
    }
}
