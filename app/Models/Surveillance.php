<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surveillance extends Model
{
    use HasContent;
    protected $table = 'surveillance';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_examen',
        'id_prof',
        'role',
    ];

    protected $primaryKey = ['id_examen', 'id_prof'];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'id_examen', 'id_examen');
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(Professeur::class, 'id_prof', 'id_prof');
    }
}
