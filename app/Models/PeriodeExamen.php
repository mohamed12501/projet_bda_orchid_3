<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeExamen extends Model
{
    use HasContent;
    protected $table = 'periode_examen';
    protected $primaryKey = 'id_periode';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'date_debut',
        'date_fin',
        'type',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function examens(): HasMany
    {
        return $this->hasMany(Examen::class, 'id_periode', 'id_periode');
    }
}
