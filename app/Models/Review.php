<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'reviewer_id', 'reviewee_id',
        'listing_id', 'type', 'rating', 'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($review) {
            // Trigger recalculateRating pada reviewee User
            $review->reviewee->recalculateRating();
            
            // Trigger recalculateRating pada Listing
            $review->listing->recalculateRating();
        });
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
