<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupe extends Model
{
    use HasContent;
    protected $table = 'groupe';
    protected $primaryKey = 'id_groupe';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'id_section',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'id_section', 'id_section');
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class, 'group_id', 'id_groupe');
    }
}
