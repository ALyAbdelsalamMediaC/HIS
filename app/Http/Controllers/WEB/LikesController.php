<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\LikeComment;
use App\Models\SubCategory;
use App\Models\User;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LikesController extends Controller
{
    public function addLike(Request $request, $mediaId)
    {
        try {
            // Find the media item
            $media = Media::findOrFail($mediaId);

            // Check if the user already liked this media
            $existingLike = Like::where('user_id', Auth::id())
                ->where('media_id', $mediaId)
                ->first();

            if ($existingLike) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => true,
                        'likesCount' => $media->likes()->count(),
                        'message' => 'You have already liked this media.'
                    ]);
                }
                return back()->with('error', 'You have already liked this media.');
            }

            // Create the like
            $like = Like::create([
                'user_id' => Auth::id(),
                'media_id' => $mediaId,
            ]);

            // Log the action
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_added',
                'description' => "Liked media: {$media->title}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => true,
                    'likesCount' => $media->likes()->count(),
                    'message' => 'Media liked successfully.'
                ]);
            }

            return back()->with('success', 'Media liked successfully.');

        } catch (\Exception $e) {
            Log::error('Like addition failed: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => false,
                    'likesCount' => isset($media) ? $media->likes()->count() : 0,
                    'message' => 'Failed to like media: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to like media: ' . $e->getMessage());
        }
    }

    /**
     * Remove a like from a media item for the authenticated user.
     *
     * @param Request $request
     * @param int $mediaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLike(Request $request, $mediaId)
    {
        try {
            // Find the like
            $like = Like::where('user_id', Auth::id())
                ->where('media_id', $mediaId)
                ->first();

            $media = Media::findOrFail($mediaId);

            if (!$like) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => false,
                        'likesCount' => $media->likes()->count(),
                        'message' => 'You have not liked this media.'
                    ]);
                }
                return back()->with('error', 'You have not liked this media.');
            }

            // Delete the like
            $like->delete();

            // Log the action
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_removed',
                'description' => "Unliked media: {$media->title}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => false,
                    'likesCount' => $media->likes()->count(),
                    'message' => 'Like removed successfully.'
                ]);
            }

            return back()->with('success', 'Like removed successfully.');

        } catch (\Exception $e) {
            Log::error('Like removal failed: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => true,
                    'likesCount' => isset($media) ? $media->likes()->count() : 0,
                    'message' => 'Failed to remove like: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to remove like: ' . $e->getMessage());
        }
    }


    public function addLikeComment(Request $request, $commentId)
    {
        try {
            // Find the comment
            $comment = Comment::findOrFail($commentId);

            // Check if the user already liked this comment
            $existingLike = LikeComment::where('user_id', Auth::id())
                ->where('comment_id', $commentId)
                ->first();

            if ($existingLike) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => true,
                        'likesCount' => LikeComment::where('comment_id', $commentId)->count(),
                        'message' => 'You have already liked this comment.'
                    ]);
                }
                return back()->with('error', 'You have already liked this comment.');
            }

            // Create the like
            $like = LikeComment::create([
                'user_id' => Auth::id(),
                'comment_id' => $commentId,
            ]);

            // Log the action
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_added',
                'description' => "Liked comment: {$comment->content}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => true,
                    'likesCount' => LikeComment::where('comment_id', $commentId)->count(),
                    'message' => 'Comment liked successfully.'
                ]);
            }

            return back()->with('success', 'Comment liked successfully.');

        } catch (\Exception $e) {
            Log::error('Like addition failed: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => false,
                    'likesCount' => isset($comment) ? LikeComment::where('comment_id', $commentId)->count() : 0,
                    'message' => 'Failed to like comment: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to like comment: ' . $e->getMessage());
        }
    }

    /**
     * Remove a like from a comment for the authenticated user.
     *
     * @param Request $request
     * @param int $commentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLikeComment(Request $request, $commentId)
    {
        try {
            // Find the like
            $like = LikeComment::where('user_id', Auth::id())
                ->where('comment_id', $commentId)
                ->first();

            $comment = Comment::findOrFail($commentId);

            if (!$like) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => false,
                        'likesCount' => LikeComment::where('comment_id', $commentId)->count(),
                        'message' => 'You have not liked this comment.'
                    ]);
                }
                return back()->with('error', 'You have not liked this comment.');
            }

            // Delete the like
            $like->delete();

            // Log the action
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_removed',
                'description' => "Unliked comment: {$comment->content}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => false,
                    'likesCount' => LikeComment::where('comment_id', $commentId)->count(),
                    'message' => 'Like removed successfully.'
                ]);
            }

            return back()->with('success', 'Like removed successfully.');

        } catch (\Exception $e) {
            Log::error('Like removal failed: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => true,
                    'likesCount' => isset($comment) ? LikeComment::where('comment_id', $commentId)->count() : 0,
                    'message' => 'Failed to remove like: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to remove like: ' . $e->getMessage());
        }
    }

    public function getLikesCommentCount($commentId)
    {
        try {
            // Find the comment
            $comment = Comment::findOrFail($commentId);

            // Count the likes for this comment
            $likeCount = LikeComment::where('comment_id', $commentId)->count();

            return response()->json([
                'success' => true,
                'like_count' => $likeCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get like count: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get like count: ' . $e->getMessage(),
            ], 500);
        }
    } 
}
