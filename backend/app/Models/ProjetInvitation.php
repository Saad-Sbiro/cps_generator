<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjetInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'projet_id',
        'inviter_id', // NEW
        'email',
        'role',
    ];

    /* ── Relationships ── */

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id', 'id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id', 'id');
    }
}
