<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectArticle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'projet_id',
        'article_id',
        'article_variant_id',
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

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ArticleVariant::class, 'article_variant_id');
    }
}
