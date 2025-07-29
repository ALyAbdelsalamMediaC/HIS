<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log as LaravelLog;


class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public static function sendNotifications(array $receiverIds, string $title, string $body, ?string $route = null)
    {
        // Validate inputs
        if (empty($receiverIds) || empty($title) || empty($body)) {
            return false;
        }

        // Get authenticated user as sender
        $sender = Auth::user();
        if (!$sender) {
            return false;
        }

        // Verify all receiver IDs exist
        $validReceiverIds = User::whereIn('id', $receiverIds)->pluck('id')->toArray();
        if (empty($validReceiverIds)) {
            return false;
        }

        // Send notifications via service
        $notificationService = app(NotificationService::class);
        $success = true;

        foreach ($validReceiverIds as $receiverId) {
            try {
                $receiver = User::find($receiverId);
                if ($receiver) {
                    $notificationService->sendNotification($sender, $receiver, $title, $body, $route);
                } else {
                    $success = false;
                }
            } catch (\Exception $e) {
                $success = false;
                \Log::error("Failed to send notification to user {$receiverId}: {$e->getMessage()}");
            }
        }

        return $success;
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'receiver_ids' => 'required|array',
            'receiver_ids.*' => 'exists:users,id',
        ]);

        // Store notifications in the database
        foreach ($request->receiver_ids as $receiverId) {
            Notification::create([
                'title' => $request->title,
                'body' => $request->body,
                'route' => null,
                'sender_id' => $receiverId,
                'receiver_id' => Auth::id(),
                'seen' => false,
            ]);

            $user1 = User::find(1);
            if (!$user1) {

                return redirect()->back()->with('error', 'Receiver user not found.');
            }

            $body = "Your have new notification";
            $user = User::find(1);
            $title = 'send notification';
            $route = "/send-notification-requests/7";
            $this->notificationService->sendNotification($request->user(), $user, $title, $body, $route);
        }

        // Example user (for testing purposes)


        return redirect()
            ->back()
            ->with('success', 'Notifications sent successfully!');
    }



    public function index()
    {
        $notifications = Notification::with(['sender', 'receiver','media'])->paginate(20);
        $unreadNotifications = Notification::where('receiver_id', auth()->id())
            ->where('seen', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        return view('pages.settings.notifications.index', compact('notifications', 'unreadNotifications'));
    }
    public function read($id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->receiver_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->seen = true;
        $notification->save();

        if ($notification->route) {
            try {
                // Define regex patterns and corresponding index routes
                $routeMap = [
                    '/media/i' => 'content.videos', // Matches "media" case-insensitive
                    
                ];

                foreach ($routeMap as $pattern => $indexRoute) {
                    if (preg_match($pattern, $notification->route)) {
                        return redirect()->route($indexRoute);
                    }
                }

                // If no match, redirect back
                return redirect()->back();
            } catch (\Exception $e) {
                LaravelLog::error('Failed to redirect to route: ' . $e->getMessage());
                return redirect()->back();
            }
        }

        return redirect()->back();
    }

    public function markAllRead(Request $request)
    {
        Notification::where('receiver_id', auth()->id())
            ->where('seen', false)
            ->update(['seen' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
