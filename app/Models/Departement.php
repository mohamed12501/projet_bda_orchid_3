<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    use HasContent;
    protected $table = 'departement';
    protected $primaryKey = 'id_dept';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'lieu',
        'id_fac',
    ];

    public function faculte(): BelongsTo
    {
        return $this->belongsTo(Faculte::class, 'id_fac', 'id_fac');
    }

    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class, 'id_dept', 'id_dept');
    }

    public function professeurs(): HasMany
    {
        return $this->hasMany(Professeur::class, 'id_dept', 'id_dept');
    }
}
