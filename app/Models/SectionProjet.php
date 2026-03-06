<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionProjet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'projet_id',
        'section_modele_id',
        'ordre',
        'contenu_final',
    ];

    protected $casts = [
        'ordre' => 'integer',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function sectionModele(): BelongsTo
    {
        return $this->belongsTo(SectionModele::class);
    }
}
