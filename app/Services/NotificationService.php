<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $messaging;

    public function __construct(?Messaging $messaging)
    {
        $this->messaging = $messaging;
        if (!$messaging) {
            Log::warning('Firebase Messaging service is not available');
        }
    }

    /**
     * Send a notification to a user and store it in the database.
     *
     * @param User $sender The user initiating the action
     * @param User $receiver The user receiving the notification
     * @param string $title Notification title
     * @param string $body Notification body
     * @param string|null $route Optional route for the notification
     * @param int|null $requestId Optional request ID to associate with the notification
     * @return void
     */
    public function sendNotification(User $sender, User $receiver, string $title, string $body, ?string $route = null, ?int $requestId = null): void
    {
        // Store the notification in the database
        Notification::create([
            'title' => $title,
            'body' => $body,
            'route' => $route,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'request_id' => $requestId,
            'seen' => false,
        ]);

        // Send FCM notification if the receiver has an fcm_token and messaging is available
        if ($receiver->fcm_token && $this->messaging) {
            $message = CloudMessage::withTarget('token', $receiver->fcm_token)
                ->withNotification([
                    'title' => $title,
                    'body' => $body,
                ]);

            try {
                $this->messaging->send($message);
                Log::info('FCM notification sent', ['receiver_id' => $receiver->id]);
            } catch (\Exception $e) {
                Log::error("Failed to send FCM notification to user {$receiver->id}: " . $e->getMessage());
            }
        } elseif ($receiver->fcm_token) {
            Log::warning('FCM notification not sent due to unavailable messaging service', ['receiver_id' => $receiver->id]);
        }
    }
}
