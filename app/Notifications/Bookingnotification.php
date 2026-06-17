<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class BookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Daftar semua event yang didukung
    const EVENT_CREATED   = 'booking_created';
    const EVENT_APPROVED  = 'booking_approved';
    const EVENT_REJECTED  = 'booking_rejected';
    const EVENT_ONGOING   = 'booking_ongoing';
    const EVENT_COMPLETED = 'booking_completed';
    const EVENT_CANCELLED = 'booking_cancelled';
    const EVENT_DISPUTED  = 'booking_disputed';

    public function __construct(
        public readonly Booking $booking,
        public readonly string $event,
    ) {}

    public function via(object $notifiable): array
    {
        // Kirim via FCM hanya jika user punya fcm_token
        return $notifiable->fcm_token ? [FcmChannel::class] : [];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        ['title' => $title, 'body' => $body] = $this->getContent();

        return FcmMessage::create()
            ->setData([
                'type'         => 'booking',
                'event'        => $this->event,
                'booking_id'   => (string) $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'listing_id'   => (string) $this->booking->listing_id,
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($title)
                    ->setBody($body)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('booking'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setSound('default')
                            ->setChannelId('booking_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('booking'))
            );
    }

    private function getContent(): array
    {
        $listingTitle = $this->booking->listing->title ?? 'Barang';
        $code         = $this->booking->booking_code;

        return match ($this->event) {
            self::EVENT_CREATED => [
                'title' => '📦 Permintaan Sewa Baru',
                'body'  => "Ada permintaan sewa untuk \"{$listingTitle}\" ({$code}). Segera konfirmasi!",
            ],
            self::EVENT_APPROVED => [
                'title' => '✅ Booking Disetujui',
                'body'  => "Booking {$code} untuk \"{$listingTitle}\" telah disetujui. Lakukan pembayaran sekarang.",
            ],
            self::EVENT_REJECTED => [
                'title' => '❌ Booking Ditolak',
                'body'  => "Booking {$code} untuk \"{$listingTitle}\" ditolak oleh peminjam.",
            ],
            self::EVENT_ONGOING => [
                'title' => '🚀 Sewa Sedang Berjalan',
                'body'  => "Pembayaran dikonfirmasi. Sewa \"{$listingTitle}\" ({$code}) sudah berjalan.",
            ],
            self::EVENT_COMPLETED => [
                'title' => '🎉 Sewa Selesai',
                'body'  => "Sewa \"{$listingTitle}\" ({$code}) telah selesai. Jangan lupa beri ulasan!",
            ],
            self::EVENT_CANCELLED => [
                'title' => '🚫 Booking Dibatalkan',
                'body'  => "Booking {$code} untuk \"{$listingTitle}\" telah dibatalkan.",
            ],
            self::EVENT_DISPUTED => [
                'title' => '⚠️ Dispute Dilaporkan',
                'body'  => "Ada laporan dispute pada booking {$code}. Tim kami akan segera meninjau.",
            ],
            default => [
                'title' => 'RentStuff',
                'body'  => "Ada pembaruan pada booking {$code}.",
            ],
        };
    }
}