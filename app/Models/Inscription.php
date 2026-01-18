<?php

namespace App\Models;

use App\Models\Traits\HasContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    use HasContent;
    protected $table = 'inscription';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_etudiant',
        'id_module',
        'note',
    ];

    protected $casts = [
        'note' => 'decimal:2',
    ];

    protected $primaryKey = ['id_etudiant', 'id_module'];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant', 'id_etudiant');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'id_module', 'id_module');
    }
}
