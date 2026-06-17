<?php

namespace App\Observers;

use App\Models\Review;
use App\Notifications\ReviewNotification;

class ReviewObserver
{
    /**
     * Dipanggil saat review baru dibuat.
     * Notifikasi ke user yang diulas (reviewee).
     */
    public function created(Review $review): void
    {
        $review->load(['reviewer', 'listing']);

        $review->reviewee->notify(new ReviewNotification($review));
    }
}