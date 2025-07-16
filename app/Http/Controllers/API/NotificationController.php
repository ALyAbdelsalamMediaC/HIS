<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
class NotificationController extends Controller
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index(Request $request)
    {
        $notifications = $this->notificationRepository->getUserNotifications($request->user()->id);

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications
        ]);
    }

    public function show(Request $request, $id)
    {
        $notification = $this->notificationRepository->getNotificationById($id, $request->user()->id);

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }

        // Mark notification as seen
        $this->notificationRepository->markAsSeen($id, $request->user()->id);

        return response()->json([
            'message' => 'Notification retrieved successfully',
            'data' => $notification
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $this->notificationRepository->updateFcmToken(
            $request->user()->id,
            $validated['fcm_token']
        );

        return response()->json([
            'message' => 'FCM token updated successfully'
        ]);
    }
}
