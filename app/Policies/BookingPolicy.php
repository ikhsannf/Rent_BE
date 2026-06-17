<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Hanya borrower atau lender terkait yang bisa lihat booking ini.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->borrower_id
            || $user->id === $booking->lender_id
            || $user->isAdmin();
    }

    /**
     * Hanya lender atau borrower terkait yang bisa update status.
     */
    public function updateStatus(User $user, Booking $booking): bool
    {
        return $user->id === $booking->borrower_id
            || $user->id === $booking->lender_id
            || $user->isAdmin();
    }
}
