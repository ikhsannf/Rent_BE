<?php

namespace App\Observers;

use App\Models\Dispute;
use App\Notifications\DisputeNotification;

class DisputeObserver
{
    /**
     * Dipanggil saat dispute baru dibuat.
     * Notifikasi ke user yang dilaporkan.
     */
    public function created(Dispute $dispute): void
    {
        $dispute->load('booking');

        $dispute->reportedUser->notify(
            new DisputeNotification($dispute, DisputeNotification::EVENT_CREATED)
        );
    }

    /**
     * Dipanggil saat dispute diupdate.
     * Jika status berubah jadi 'resolved', notifikasi ke kedua pihak.
     */
    public function updated(Dispute $dispute): void
    {
        if (!$dispute->wasChanged('status')) {
            return;
        }

        if ($dispute->status === 'resolved') {
            $dispute->load('booking');

            $notification = new DisputeNotification($dispute, DisputeNotification::EVENT_RESOLVED);

            // Notifikasi ke pelapor dan yang dilaporkan
            $dispute->reportedBy->notify($notification);
            $dispute->reportedUser->notify($notification);
        }
    }
}