<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;

class NotificationRepository
{
    protected $notification;
    protected $user;

    public function __construct(Notification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user = $user;
    }

    public function getUserNotifications($userId)
    {
        return $this->notification
            ->where('receiver_id', $userId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getNotificationById($notificationId, $userId)
    {
        return $this->notification
            ->where('id', $notificationId)
            ->where('receiver_id', $userId)
            ->with('sender')
            ->first();
    }

    public function markAsSeen($notificationId, $userId)
    {
        return $this->notification
            ->where('id', $notificationId)
            ->where('receiver_id', $userId)
            ->update(['seen' => true]);
    }

    public function updateFcmToken($userId, $fcmToken)
    {
        return $this->user->findOrFail($userId)
            ->update(['fcm_token' => $fcmToken]);
    }
}
