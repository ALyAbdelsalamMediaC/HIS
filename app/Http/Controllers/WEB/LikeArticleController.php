<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommentArticle;
use App\Models\LikeArticle;
use App\Models\LikeCommentArticle;
use App\Models\Log;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
class LikeArticleController extends Controller
{
     public function addLike(Request $request, $mediaId)
    {
        try {
            $media = Article::findOrFail($mediaId);

            $existingLike = LikeArticle::where('user_id', Auth::id())
                ->where('article_id', $mediaId)
                ->first();

            if ($existingLike) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => true,
                        'likesCount' => $media->likesarticle()->count(),
                        'message' => 'You have already liked this article.'
                    ]);
                }
                return back()->with('error', 'You have already liked this article.');
            }

            $like = LikeArticle::create([
                'user_id' => Auth::id(),
                'article_id' => $mediaId,
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_added',
                'description' => "Liked article: {$media->title}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => true,
                    'likesCount' => $media->likesarticle()->count(),
                    'message' => 'Article liked successfully.'
                ]);
            }

            return back()->with('success', 'Article liked successfully.');
        } catch (\Exception $e) {
            \Log::error('Like addition failed: ' . $e->getMessage());
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => false,
                    'likesCount' => isset($media) ? $media->likesarticle()->count() : 0,
                    'message' => 'Failed to like article: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to like article: ' . $e->getMessage());
        }
    }

    public function removeLike(Request $request, $mediaId)
    {
        try {
            $like = LikeArticle::where('user_id', Auth::id())
                ->where('article_id', $mediaId)
                ->first();

            $media = Article::findOrFail($mediaId);

            if (!$like) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => false,
                        'likesCount' => $media->likesarticle()->count(),
                        'message' => 'You have not liked this article.'
                    ]);
                }
                return back()->with('error', 'You have not liked this article.');
            }

            $like->delete();

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_removed',
                'description' => "Unliked article: {$media->title}",
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => false,
                    'likesCount' => $media->likesarticle()->count(),
                    'message' => 'Like removed successfully.'
                ]);
            }

            return back()->with('success', 'Like removed successfully.');
        } catch (\Exception $e) {
            \Log::error('Like removal failed: ' . $e->getMessage());
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => true,
                    'likesCount' => isset($media) ? $media->likesarticle()->count() : 0,
                    'message' => 'Failed to remove like: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to remove like: ' . $e->getMessage());
        }
    }


    public function addLikeComment(Request $request, $commentId)
    {
        try {
            $comment = CommentArticle::findOrFail($commentId);
            $existingLike = LikeCommentArticle::where('user_id', Auth::id())
                ->where('comment_id', $commentId)
                ->first();
            if ($existingLike) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => true,
                        'likesCount' => LikeCommentArticle::where('comment_id', $commentId)->count(),
                        'message' => 'You have already liked this comment.'
                    ]);
                }
                return back()->with('error', 'You have already liked this comment.');
            }
            $like = LikeCommentArticle::create([
                'user_id' => Auth::id(),
                'comment_id' => $commentId,
            ]);
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_added',
                'description' => "Liked comment: {$comment->content}",
            ]);
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => true,
                    'likesCount' => LikeCommentArticle::where('comment_id', $commentId)->count(),
                    'message' => 'Comment liked successfully.'
                ]);
            }
            return back()->with('success', 'Comment liked successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => false,
                    'likesCount' => isset($comment) ? LikeCommentArticle::where('comment_id', $commentId)->count() : 0,
                    'message' => 'Failed to like comment: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to like comment: ' . $e->getMessage());
        }
    }

    public function removeLikeComment(Request $request, $commentId)
    {
        try {
            $like = LikeCommentArticle::where('user_id', Auth::id())
                ->where('comment_id', $commentId)
                ->first();
            $comment = CommentArticle::findOrFail($commentId);
            if (!$like) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'liked' => false,
                        'likesCount' => LikeCommentArticle::where('comment_id', $commentId)->count(),
                        'message' => 'You have not liked this comment.'
                    ]);
                }
                return back()->with('error', 'You have not liked this comment.');
            }
            $like->delete();
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'like_removed',
                'description' => "Unliked comment: {$comment->content}",
            ]);
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => false,
                    'likesCount' => LikeCommentArticle::where('comment_id', $commentId)->count(),
                    'message' => 'Like removed successfully.'
                ]);
            }
            return back()->with('success', 'Like removed successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'liked' => true,
                    'likesCount' => isset($comment) ? LikeCommentArticle::where('comment_id', $commentId)->count() : 0,
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
            $comment = CommentArticle::findOrFail($commentId);

            // Count the likes for this comment
            $likeCount = LikeCommentArticle::where('comment_id', $commentId)->count();

            return response()->json([
                'success' => true,
                'like_count' => $likeCount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get like count: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get like count: ' . $e->getMessage(),
            ], 500);
        }
    } 

}
