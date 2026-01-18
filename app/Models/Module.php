<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasContent;
    protected $table = 'module';
    protected $primaryKey = 'id_module';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'credits',
        'id_formation',
        'pre_requis_id',
        'necessite_equipement',
    ];

    protected $casts = [
        'credits' => 'integer',
        'necessite_equipement' => 'boolean',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'id_formation', 'id_formation');
    }

    public function preRequis(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'pre_requis_id', 'id_module');
    }

    public function preRequisDe(): HasMany
    {
        return $this->hasMany(Module::class, 'pre_requis_id', 'id_module');
    }

    public function etudiants(): BelongsToMany
    {
        return $this->belongsToMany(Etudiant::class, 'inscription', 'id_module', 'id_etudiant')
            ->withPivot('note');
    }

    public function examens(): HasMany
    {
        return $this->hasMany(Examen::class, 'id_module', 'id_module');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_module', 'id_module');
    }
}
