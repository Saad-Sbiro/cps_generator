<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleVariant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'article_id',
        'label',
        'contenu',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function projectArticles(): HasMany
    {
        return $this->hasMany(ProjectArticle::class);
    }
}
