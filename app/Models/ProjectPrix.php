<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPrix extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_prix';

    protected $fillable = [
        'projet_id',
        'prix_catalogue_id',
        'numero_prix',
        'quantite',
        'prix_unitaire_ht',
        'ordre',
    ];

    protected $casts = [
        'numero_prix'      => 'integer',
        'quantite'         => 'decimal:2',
        'prix_unitaire_ht' => 'decimal:2',
        'total_ht'         => 'decimal:2',
        'ordre'            => 'integer',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function prixCatalogue(): BelongsTo
    {
        return $this->belongsTo(PrixCatalogue::class, 'prix_catalogue_id');
    }
}
