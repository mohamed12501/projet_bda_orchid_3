<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Universite extends Model
{
    use HasContent;
    protected $table = 'universite';
    protected $primaryKey = 'id_univ';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'ville',
    ];

    public function facultes(): HasMany
    {
        return $this->hasMany(Faculte::class, 'id_univ', 'id_univ');
    }
}

