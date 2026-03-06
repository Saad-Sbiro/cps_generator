<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'reference',
        'intitule',
        'date_creation',
        'taux_tva',
        'inclure_brd_dans_cps',
        'maitre_ouvrage',
        'objet_marche',
        'lieu',
        'delai_execution',
    ];

    protected $casts = [
        'date_creation'      => 'date',
        'taux_tva'           => 'decimal:2',
        'inclure_brd_dans_cps' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LignePrixProjet::class)->orderBy('ordre');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SectionProjet::class)->orderBy('ordre');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ExportDocument::class)->latest();
    }

    // ---- Computed totals ----
    public function getTotalHtAttribute(): float
    {
        return (float) $this->lignes->sum('total_ht');
    }

    public function getTotalTvaAttribute(): float
    {
        return round($this->total_ht * ($this->taux_tva / 100), 2);
    }

    public function getTotalTtcAttribute(): float
    {
        return round($this->total_ht + $this->total_tva, 2);
    }

    protected $appends = ['total_ht', 'total_tva', 'total_ttc'];
}
