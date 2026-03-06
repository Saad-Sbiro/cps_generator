<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectionModele extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'titre',
        'type',
        'contenu',
        'ordre_defaut',
    ];

    public function sectionProjets(): HasMany
    {
        return $this->hasMany(SectionProjet::class);
    }
}
