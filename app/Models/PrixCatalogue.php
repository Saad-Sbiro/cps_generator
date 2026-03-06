<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrixCatalogue extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'categorie',
        'sous_categorie',
        'designation',
        'unite',
        'prix_unitaire_ht_defaut',
    ];

    protected $casts = [
        'prix_unitaire_ht_defaut' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projectPrix(): HasMany
    {
        return $this->hasMany(ProjectPrix::class);
    }
}
