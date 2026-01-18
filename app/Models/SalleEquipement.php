<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalleEquipement extends Model
{
    use HasContent;
    protected $table = 'salle_equipement';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_salle',
        'id_equipement',
        'quantite',
    ];

    protected $casts = [
        'quantite' => 'integer',
    ];

    protected $primaryKey = ['id_salle', 'id_equipement'];

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class, 'id_equipement', 'id_equipement');
    }
}
