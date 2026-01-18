<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etudiant extends Model
{
    use HasContent;
    protected $table = 'etudiant';
    protected $primaryKey = 'id_etudiant';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'email',
        'promo',
        'id_formation',
        'section_id',
        'group_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'promo' => 'integer',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'id_formation', 'id_formation');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'inscription', 'id_etudiant', 'id_module')
            ->withPivot('note');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_etudiant', 'id_etudiant');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id_section');
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'group_id', 'id_groupe');
    }
}
