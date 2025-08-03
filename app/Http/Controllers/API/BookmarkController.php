<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Bookmark;
use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

class BookmarkController extends Controller
{
 protected $notificationService;
    public function __construct(
        NotificationService $notificationService
    ) {

        $this->notificationService = $notificationService;
    }
    public function addBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'flag' => 'required|string|max:255',
        ], [
            'user_id.exists' => 'The specified user does not exist.',
            'media_id.exists' => 'The specified media does not exist.',
        ]);

        // Ensure exactly one of article_id or media_id is provided
        if (!$request->has('media_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exactly one media_id must be provided.'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = (int) $request->input('user_id');
        $mediaId =  (int) $request->input('media_id');
        $flag =  (int) $request->input('flag');
        $media = Media::where('id', $mediaId)->where('status','published')->first();
        
        // Check if bookmark already exists
        $existingBookmark = Bookmark::where('user_id', $userId)
            ->where(function ($query) use ($mediaId) {
                if ($mediaId) {
                    $query->where('media_id', $mediaId)->whereNull('article_id');
                }
            })->first();

        if ($existingBookmark) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark already exists'
            ], 200);
        }

        // Create new bookmark
        $bookmark = new Bookmark();
        $bookmark->user_id = $userId;
        $bookmark->flag = $flag;
        $bookmark->media_id = $mediaId;
        $bookmark->save();

         $sender = User::find($userId);
            $user_media = Media::where('id',$mediaId)->with('user')->first();
            $receiver = $user_media->user;
            $title = "New bookmark on media id: " . $mediaId ;
            $body = "The use" . $sender->name . " made bookmark on the media id "  . $mediaId ;
            $route = "content/videos/" . $media->id ."/". $media->status;

            $this->notificationService->sendNotification(
                $sender,
                $receiver,
                $title,
                $body,
                $route,
                $media->id
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark added successfully',
            'data' => $bookmark
        ], 201);
    }


    public function removeBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'article_id' => 'nullable|integer|exists:articles,id',
            'media_id' => 'nullable|integer|exists:media,id',
        ], [
            'user_id.exists' => 'The specified user does not exist.',
            'article_id.exists' => 'The specified article does not exist.',
            'media_id.exists' => 'The specified media does not exist.',
        ]);

        // Ensure exactly one of article_id or media_id is provided
        if ((!$request->has('article_id') && !$request->has('media_id')) || ($request->has('article_id') && $request->has('media_id'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exactly one of article_id or media_id must be provided.'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->input('user_id');
        $articleId = $request->input('article_id');
        $mediaId = $request->input('media_id');

        $bookmark = Bookmark::where('user_id', $userId)
            ->where(function ($query) use ($articleId, $mediaId) {
                if ($articleId) {
                    $query->where('article_id', $articleId)->whereNull('media_id');
                } else {
                    $query->where('media_id', $mediaId)->whereNull('article_id');
                }
            })->first();

        if (!$bookmark) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark removed successfully'
        ], 200);
    }


    public function getBookmarks()
    {
        $userId = auth()->check() ? auth()->user()->id : null;

        $mediaLikes = Media::where('user_id', $userId)
            ->withCount(['likes', 'comments'])
            ->get();

        $mediaLikes = $mediaLikes->isEmpty() ? null : $mediaLikes;
        $mediaBookmarks = Bookmark::where('user_id',$userId)->with(['media' => function ($query) use ($userId) {
            $query->where('status', 'published')
                ->withCount(['comments', 'likes'])
                ->withExists(['likes as is_liked' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }]);
        }])
            ->whereHas('media', function ($query) {
                $query->where('status', 'published');
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $mediaBookmarks = $mediaBookmarks->isEmpty() ? null : $mediaBookmarks;
        return response()->json([
            'status' => 'success',
            'data' => [
                'bookmarks' => $mediaBookmarks,
                'mediaLikes' => $mediaLikes,
            ]
        ], 200);
    }
}
