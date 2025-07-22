<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use App\Models\Media;
use App\Models\Comment;
use App\Models\Like;
use App\Models\LikeComment;
use App\Models\Log as ModelsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikesController extends Controller
{
    public function addLike(Request $request,$mediaId)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);
            
            $userId = $request->input('user_id');
            
            // Find the media item
            $media = Media::where('id',$mediaId)->first();
            // Check if the user already liked this media
            $existingLike = Like::where('user_id', $userId)
                ->where('media_id', $mediaId)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already liked this media.'
                ], 400);
            }

            // Create the like
            $like = Like::create([
                'user_id' => $userId,
                'media_id' => $mediaId,
            ]);

            // Log the action
            ModelsLog::create([
                'user_id' => $userId,
                'type' => 'like_added',
                'description' => "Liked media: {$media->title}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media liked successfully.',
                'data' => [
                    'like_id' => $like->id,
                    'media_id' => $mediaId
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Like addition failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to like media: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeLike(Request $request,$mediaId)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $userId = $request->input('user_id');

            // Find the like
            $like = Like::where('user_id', $userId)
                ->where('media_id', $mediaId)
                ->first();

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not liked this media.'
                ], 400);
            }

            // Get media title before deleting like for logging
            $media = Media::findOrFail($mediaId);

            // Delete the like
            $like->delete();

            // Log the action
            ModelsLog::create([
                'user_id' => $userId,
                'type' => 'like_removed',
                'description' => "Unliked media: {$media->title}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Like removed successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Like removal failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove like: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addLikeComment(Request $request,$commentId)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $userId = $request->input('user_id');

            // Find the comment
            $comment = Comment::findOrFail($commentId);

            // Check if the user already liked this comment
            $existingLike = LikeComment::where('user_id', $userId)
                ->where('comment_id', $commentId)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already liked this comment.'
                ], 400);
            }

            // Create the like
            $like = LikeComment::create([
                'user_id' => $userId,
                'comment_id' => $commentId,
            ]);

            // Log the action
            ModelsLog::create([
                'user_id' => $userId,
                'type' => 'like_added',
                'description' => "Liked comment: {$comment->content}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment liked successfully.',
                'data' => [
                    'like_id' => $like->id,
                    'comment_id' => $commentId
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Like addition failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to like comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeLikeComment(Request $request ,$commentId)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $userId = $request->input('user_id');

            // Find the like
            $like = LikeComment::where('user_id', $userId)
                ->where('comment_id', $commentId)
                ->first();

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not liked this comment.'
                ], 400);
            }

            // Get comment content before deleting like for logging
            $comment = Comment::findOrFail($commentId);

            // Delete the like
            $like->delete();

            // Log the action
            ModelsLog::create([
                'user_id' => $userId,
                'type' => 'like_removed',
                'description' => "Unliked comment: {$comment->content}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Like removed successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Like removal failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove like: ' . $e->getMessage()
            ], 500);
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
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get like count: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get like count: ' . $e->getMessage(),
            ], 500);
        }
    }
}