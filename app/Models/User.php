<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'phone', 'address', 'avatar',
        'is_verified', 'is_active',
        'rating', 'review_count', 'fcm_token',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_verified'       => 'boolean',
        'is_active'         => 'boolean',
        'rating'            => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function borrowerBookings()
    {
        return $this->hasMany(Booking::class, 'borrower_id');
    }

    public function lenderBookings()
    {
        return $this->hasMany(Booking::class, 'lender_id');
    }

    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public function isLender(): bool  { return $this->role === 'lender'; }
    public function isBorrower(): bool { return $this->role === 'borrower'; }
    public function isAdmin(): bool   { return $this->role === 'admin'; }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) return null;
        return str_starts_with($this->avatar, 'http')
            ? $this->avatar
            : asset('storage/' . $this->avatar);
    }

    /**
     * Recalculate and save average rating from reviews.
     */
    public function recalculateRating(): void
    {
        $avg = $this->receivedReviews()->avg('rating') ?? 0;
        $count = $this->receivedReviews()->count();
        $this->update(['rating' => round($avg, 2), 'review_count' => $count]);
    }
}