<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'lancore_user_id',
    'display_name',
    'avatar_url',
    'lancore_synced_at',
    'role',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'two_factor_confirmed_at' => 'datetime',
            'lancore_synced_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /** @return HasMany<Ticket, $this> */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /** @return HasMany<Ticket, $this> */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    /** @return HasMany<TicketReply, $this> */
    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'author_id');
    }

    // ── Role helpers ─────────────────────────────────────────────────────────

    public function isStaff(): bool
    {
        return in_array($this->role, [UserRole::Staff, UserRole::Admin]);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isLanCoreUser(): bool
    {
        return $this->lancore_user_id !== null;
    }

    // ── Display helpers ──────────────────────────────────────────────────────

    public function displayName(): string
    {
        return $this->display_name ?? $this->name;
    }

    // ── Auth overrides ───────────────────────────────────────────────────────

    /**
     * LanCore SSO users have no local password and must not receive password
     * reset emails. We silently skip the notification so that the forgot-password
     * form still shows a success message without leaking user status.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        if ($this->isLanCoreUser()) {
            return;
        }

        parent::sendPasswordResetNotification($token);
    }
}
