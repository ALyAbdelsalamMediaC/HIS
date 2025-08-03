<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminComment;
use App\Models\Media;
use App\Models\Rate;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminCommentController extends Controller
{
    protected $notificationService;
    public function __construct(
        NotificationService $notificationService
    ) {

        $this->notificationService = $notificationService;
    }
    public function showAdminComment(Request $request)
    {
        try {
            $media_id = $request->media_id;
            $comments = AdminComment::where('media_id', $media_id)
                ->whereNull('parent_id')->with('user')
                ->with(['replies' => function ($query) {
                    $query->select('id', 'user_id', 'media_id', 'parent_id', 'content', 'created_at', 'updated_at')->with('user');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'AdminComments' => $comments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    public function addComment(Request $request)
    {
        try {
            // Validate the request

            // Get the authenticated user
            $userId = $request->user_id;
            $media_id = $request->media_id;

            $Media = Media::where('id', $media_id)->where('status', 'pending')->first();

            // Verify media exists
            if (!$Media) {
                return redirect()->back()
                    ->with('error', 'The specified media does not exist.')
                    ->withInput();
            }

            // Create the comment
            $comment = AdminComment::create([
                'user_id' => $userId,
                'media_id' => $media_id,
                'parent_id' => null,
                'content' => $request->content,
            ]);
            $comment->load('user');
            $sender = User::find($userId);
            $user_media = Media::where('id', $media_id)->with('user')->first();
            $receiver = $user_media->user;
            $title = "New admin comment on media id: " . $media_id;
            $body = "content: " . $request->content;
            $route = "content/videos/" . $media_id . "/" . $Media->status;

            $this->notificationService->sendNotification(
                $sender,
                $receiver,
                $title,
                $body,
                $route,
                $media_id
            );

            return response()->json([
                'success' => true,
                'comment' => 'Comment added successfully.',
                'comment' => $comment,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Media not found.')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the comment.')
                ->withInput();
        }
    }

    public function reply(Request $request)
    {
        try {

            $userId = $request->user_id;
            $mediaId = $request->media_id;
            $parentId = $request->parent_id;
            $content = $request->content;


            // Verify media and parent comment exist
            $media = Media::findOrFail($mediaId)->where('status', 'pending')->first();
            $parentComment = AdminComment::findOrFail($parentId);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->media_id != $mediaId) {
                return redirect()->back()
                    ->with('error', 'The parent comment does not belong to the specified media.')
                    ->withInput();
            }

            // Create the reply
            $comment = AdminComment::create([
                'user_id' => $userId,
                'media_id' => $mediaId,
                'parent_id' => $parentId,
                'content' => $content,
            ]);

            $comment->load('user');
            $sender = User::find($userId);
            $user_media = Media::where('id', $mediaId)->with('user')->first();
            $receiver = $user_media->user;
            $title = "New admin comment on media id: " . $mediaId;
            $body = "content: " . $request->content;
            $route = "content/videos/" . $mediaId . "/" . $user_media->status;

            $this->notificationService->sendNotification(
                $sender,
                $receiver,
                $title,
                $body,
                $route,
                $mediaId
            );
            return response()->json([
                'success' => true,
                'comment' => 'Reply added successfully.',

                'comment' => $comment,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Media or parent comment not found.')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the reply.')
                ->withInput();
        }
    }
}
