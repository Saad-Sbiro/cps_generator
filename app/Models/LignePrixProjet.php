<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LignePrixProjet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'projet_id',
        'poste_id',
        'numero_prix',
        'quantite',
        'prix_unitaire_ht',
        'ordre',
    ];

    protected $casts = [
        'quantite'         => 'decimal:3',
        'prix_unitaire_ht' => 'decimal:2',
        'total_ht'         => 'decimal:2',
        'ordre'            => 'integer',
        'numero_prix'      => 'integer',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function cataloguePoste(): BelongsTo
    {
        return $this->belongsTo(CataloguePoste::class, 'poste_id');
    }
}
