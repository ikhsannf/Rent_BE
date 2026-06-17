<?php

namespace App\Notifications;

use App\Models\Review;
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

class ReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Review $review,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable->fcm_token ? [FcmChannel::class] : [];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $reviewerName = $this->review->reviewer->name ?? 'Seseorang';
        $stars        = str_repeat('⭐', $this->review->rating);
        $listingTitle = $this->review->listing->title ?? 'listing kamu';

        return FcmMessage::create()
            ->setData([
                'type'       => 'review',
                'review_id'  => (string) $this->review->id,
                'listing_id' => (string) $this->review->listing_id,
                'booking_id' => (string) $this->review->booking_id,
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle('⭐ Ulasan Baru')
                    ->setBody("{$reviewerName} memberi rating {$stars} untuk \"{$listingTitle}\".")
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('review'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setSound('default')
                            ->setChannelId('review_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('review'))
            );
    }
}