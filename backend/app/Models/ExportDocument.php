<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportDocument extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'projet_id',
        'type',
        'disk',
        'filename',
        'path',
    ];

    protected $attributes = [
        'disk' => 'local',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $appends = ['download_url'];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('exports.download', $this->id, false);
    }
}
