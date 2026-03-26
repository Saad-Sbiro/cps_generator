<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'titre',
        'type',
        'contenu',
        'ordre_defaut',
    ];

    protected $casts = [
        'ordre_defaut' => 'integer',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ArticleVariant::class);
    }

    public function defaultVariant(): ?ArticleVariant
    {
        return $this->variants()->where('is_default', true)->first();
    }

    public function projectArticles(): HasMany
    {
        return $this->hasMany(ProjectArticle::class);
    }
}
