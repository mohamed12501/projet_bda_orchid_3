<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formation extends Model
{
    use HasContent;
    protected $table = 'formation';
    protected $primaryKey = 'id_formation';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'niveau',
        'nb_modules',
        'id_dept',
    ];

    protected $casts = [
        'nb_modules' => 'integer',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'id_dept', 'id_dept');
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class, 'id_formation', 'id_formation');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'id_formation', 'id_formation');
    }
}
