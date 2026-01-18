<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Professeur extends Model
{
    use HasContent;
    protected $table = 'professeur';
    protected $primaryKey = 'id_prof';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'email',
        'grade',
        'id_dept',
        'nb_surveillances_periode',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'nb_surveillances_periode' => 'integer',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'id_dept', 'id_dept');
    }

    public function examens(): BelongsToMany
    {
        return $this->belongsToMany(Examen::class, 'surveillance', 'id_prof', 'id_examen')
            ->withPivot('role');
    }

    public function surveillances()
    {
        return $this->hasMany(Surveillance::class, 'id_prof', 'id_prof');
    }
}
