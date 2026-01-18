<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsersMeta extends Model
{
    use HasContent;
    protected $table = 'users_meta';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id',
        'role',
        'dept_id',
        'formation_id',
        'id_prof',
        'id_etudiant',
    ];

    protected $casts = [
        'dept_id' => 'integer',
        'formation_id' => 'integer',
        'id_prof' => 'integer',
        'id_etudiant' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id', 'id');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'dept_id', 'id_dept');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id', 'id_formation');
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(Professeur::class, 'id_prof', 'id_prof');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant', 'id_etudiant');
    }
}
