<?php

namespace App\Observers;

use App\Models\Booking;
use App\Notifications\BookingNotification;

class BookingObserver
{
    /**
     * Dipanggil saat booking pertama kali dibuat.
     * Notifikasi ke LENDER: ada permintaan sewa baru.
     */
    public function created(Booking $booking): void
    {
        $booking->load('listing');

        $booking->lender->notify(
            new BookingNotification($booking, BookingNotification::EVENT_CREATED)
        );
    }

    /**
     * Dipanggil setiap kali booking diupdate.
     * Kita cek apakah field 'status' berubah, lalu kirim notifikasi ke pihak yang tepat.
     */
    public function updated(Booking $booking): void
    {
        // Hanya proses jika status berubah
        if (!$booking->wasChanged('status')) {
            return;
        }

        $booking->load('listing');
        $newStatus = $booking->status;

        match ($newStatus) {
            // Lender approve → notif ke BORROWER
            'approved' => $booking->borrower->notify(
                new BookingNotification($booking, BookingNotification::EVENT_APPROVED)
            ),

            // Lender reject → notif ke BORROWER
            'rejected' => $booking->borrower->notify(
                new BookingNotification($booking, BookingNotification::EVENT_REJECTED)
            ),

            // Borrower upload bukti bayar → notif ke LENDER
            'ongoing' => $booking->lender->notify(
                new BookingNotification($booking, BookingNotification::EVENT_ONGOING)
            ),

            // Lender tandai selesai → notif ke BORROWER
            'completed' => $booking->borrower->notify(
                new BookingNotification($booking, BookingNotification::EVENT_COMPLETED)
            ),

            // Borrower cancel → notif ke LENDER
            'cancelled' => $booking->lender->notify(
                new BookingNotification($booking, BookingNotification::EVENT_CANCELLED)
            ),

            // Dispute dibuat → notif ke KEDUA pihak
            'disputed' => $this->notifyBothParties(
                $booking, BookingNotification::EVENT_DISPUTED
            ),

            default => null,
        };
    }

    private function notifyBothParties(Booking $booking, string $event): void
    {
        $notification = new BookingNotification($booking, $event);
        $booking->borrower->notify($notification);
        $booking->lender->notify($notification);
    }
}