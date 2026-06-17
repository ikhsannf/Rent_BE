<?php

namespace App\Notifications;

use App\Models\Dispute;
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

class DisputeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const EVENT_CREATED  = 'dispute_created';
    const EVENT_RESOLVED = 'dispute_resolved';

    public function __construct(
        public readonly Dispute $dispute,
        public readonly string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable->fcm_token ? [FcmChannel::class] : [];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        ['title' => $title, 'body' => $body] = $this->getContent();

        return FcmMessage::create()
            ->setData([
                'type'       => 'dispute',
                'event'      => $this->event,
                'dispute_id' => (string) $this->dispute->id,
                'booking_id' => (string) $this->dispute->booking_id,
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($title)
                    ->setBody($body)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('dispute'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setSound('default')
                            ->setChannelId('dispute_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('dispute'))
            );
    }

    private function getContent(): array
    {
        $bookingCode = $this->dispute->booking->booking_code ?? '-';

        return match ($this->event) {
            self::EVENT_CREATED => [
                'title' => '⚠️ Kamu Dilaporkan',
                'body'  => "Ada laporan dispute pada booking {$bookingCode}. Tim kami akan meninjau dalam 1x24 jam.",
            ],
            self::EVENT_RESOLVED => [
                'title' => '✅ Dispute Selesai',
                'body'  => "Dispute pada booking {$bookingCode} telah diselesaikan oleh admin.",
            ],
            default => [
                'title' => 'RentStuff',
                'body'  => "Ada pembaruan pada dispute booking {$bookingCode}.",
            ],
        };
    }
}