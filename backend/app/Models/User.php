<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ── Collaboration Relationships ── */

    /**
     * Projects this user has been granted access to directly.
     */
    public function sharedProjets(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'projet_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role (or higher) in a project.
     * Owner (created the project) has all roles implicitly.
     * Editor can do Editor/Viewer things.
     */
    public function hasProjetRole(Projet $projet, string $role): bool
    {
        // Owner always has the highest role
        if ((string) $projet->user_id === (string) $this->id) {
            return true;
        }

        $userRole = $this->roleInProjet($projet);

        if (! $userRole) {
            return false;
        }

        if ($role === 'viewer') {
            return in_array($userRole, ['editor', 'viewer']);
        }

        if ($role === 'editor') {
            return $userRole === 'editor';
        }

        return false;
    }

    /**
     * Get the explicitly assigned role for a user in a project.
     * Returns null if not explicitly shared, even if they are the owner.
     */
    public function roleInProjet(Projet $projet): ?string
    {
        $shared = $this->sharedProjets()->where('projet_id', $projet->id)->first();
        return $shared ? $shared->pivot->role : null;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $resetUrl = "{$frontendUrl}/reset-password?token={$token}&email=" . urlencode($this->email);

        try {
            \Illuminate\Support\Facades\Http::withToken('re_WZqix96S_MPxni24AYPfCEKFjTjMqZdy4')
                ->post('https://api.resend.com/emails', [
                    'from' => 'onboarding@resend.dev',
                    'to' => $this->email,
                    'subject' => 'Reset Password Notification',
                    'html' => "<p>You are receiving this email because we received a password reset request for your account.</p>
                               <p><a href=\"{$resetUrl}\">Reset Password</a></p>
                               <p>If you did not request a password reset, no further action is required.</p>"
                ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send reset password email: ' . $e->getMessage());
        }
    }
}

