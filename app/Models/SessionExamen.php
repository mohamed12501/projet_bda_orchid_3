<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionExamen extends Model
{
    use HasContent;
    protected $table = 'session_examen';
    protected $primaryKey = 'id_session';
    public $timestamps = false;

    protected $fillable = [
        'id_examen',
        'id_salle',
        'nb_places_allouees',
    ];

    protected $casts = [
        'nb_places_allouees' => 'integer',
    ];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'id_examen', 'id_examen');
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }
}
