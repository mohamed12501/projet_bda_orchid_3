<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculte extends Model
{
    use HasContent;
    protected $table = 'faculte';
    protected $primaryKey = 'id_fac';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'id_univ',
    ];

    public function universite(): BelongsTo
    {
        return $this->belongsTo(Universite::class, 'id_univ', 'id_univ');
    }

    public function departements(): HasMany
    {
        return $this->hasMany(Departement::class, 'id_fac', 'id_fac');
    }
}
