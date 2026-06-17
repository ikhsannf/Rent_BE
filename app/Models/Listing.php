<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'description',
        'price_per_day', 'deposit', 'condition', 'status',
        'location', 'brand', 'model',
        'min_rent_days', 'max_rent_days',
        'average_rating', 'review_count', 'total_bookings',
        'is_featured',
    ];

    protected $casts = [
        'price_per_day'  => 'float',
        'deposit'        => 'float',
        'average_rating' => 'float',
        'is_featured'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function lender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('sort_order');
    }

    public function primaryPhoto()
    {
        return $this->hasOne(ListingPhoto::class)->where('is_primary', true);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeSearch($query, string $keyword)
    {
        if (config('database.default') === 'sqlite') {
            return $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }
        return $query->whereFullText(['title', 'description'], $keyword);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $s) => $q->search($s))
            ->when($filters['category_id'] ?? null, fn($q, $c) => $q->where('category_id', $c))
            ->when($filters['min_price'] ?? null, fn($q, $p) => $q->where('price_per_day', '>=', $p))
            ->when($filters['max_price'] ?? null, fn($q, $p) => $q->where('price_per_day', '<=', $p))
            ->when($filters['min_rating'] ?? null, fn($q, $r) => $q->where('average_rating', '>=', $r))
            ->when($filters['location'] ?? null, fn($q, $l) => $q->where('location', 'like', "%{$l}%"))
            ->when($filters['condition'] ?? null, fn($q, $c) => $q->where('condition', $c));
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Check if listing is booked on given date range.
     */
    public function isBookedBetween(\Carbon\Carbon $start, \Carbon\Carbon $end): bool
    {
        return $this->bookings()
            ->whereIn('status', ['approved', 'ongoing'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->exists();
    }

    /**
     * Recalculate average rating from reviews.
     */
    public function recalculateRating(): void
    {
        $avg   = $this->reviews()->avg('rating') ?? 0;
        $count = $this->reviews()->count();
        $this->update(['average_rating' => round($avg, 2), 'review_count' => $count]);
    }
}