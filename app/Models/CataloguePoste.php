<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CataloguePoste extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'designation',
        'unite',
        'prix_unitaire_ht_defaut',
        'description_technique',
        'categorie',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'prix_unitaire_ht_defaut' => 'decimal:2',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(LignePrixProjet::class, 'poste_id');
    }
}
