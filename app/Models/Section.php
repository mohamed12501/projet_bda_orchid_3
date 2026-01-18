<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasContent;
    protected $table = 'section';
    protected $primaryKey = 'id_section';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'id_formation',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'id_formation', 'id_formation');
    }

    public function groupes(): HasMany
    {
        return $this->hasMany(Groupe::class, 'id_section', 'id_section');
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class, 'section_id', 'id_section');
    }
}
