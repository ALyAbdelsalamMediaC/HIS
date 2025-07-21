<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;

use App\Models\Comment;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentController extends Controller
{
    // public function showAddCommentForm($media_id)
    // {
    //     // Pass media_id to the view
    //     return view('pages.content.add-comment', compact('media_id'));
    // }

    public function addComment(Request $request, $media_id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                        'message' => 'Validation failed.'
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get the authenticated user
            $user = auth()->user();
            if (!$user || empty($user->email)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must be logged in with a valid email address to comment.'
                    ], 401);
                }
                return redirect()->back()
                    ->with('error', 'You must be logged in with a valid email address to comment.')
                    ->withInput();
            }

            // Verify media exists
            if (!Media::find($media_id)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The specified media does not exist.'
                    ], 404);
                }
                return redirect()->back()
                    ->with('error', 'The specified media does not exist.')
                    ->withInput();
            }

            // Create the comment
            $comment = Comment::create([
                'user_id' => $user->id,
                'media_id' => $media_id,
                'parent_id' => null,
                'content' => $request->content,
            ]);

            if ($request->expectsJson()) {
                $comment = $comment->fresh(['user', 'replies.user']);
                return response()->json([
                    'success' => true,
                    'comment' => $comment,
                    'message' => 'Comment added successfully.'
                ], 201); // Use 201 Created for successful creation
            }

            return redirect()->back()
                ->with('success', 'Comment added successfully.');

        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Media not found.')
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while adding the comment.'
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the comment.')
                ->withInput();
        }
    }

    public function showReplyForm($media_id, $parent_id)
    {
        // Pass media_id and parent_id to the view
        return view('pages.content.video.single_video_published', compact('media_id', 'parent_id'));
    }

    public function reply(Request $request, $media_id, $parent_id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                        'message' => 'Validation failed.'
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get the authenticated user
            $user = auth()->user();
            if (!$user || empty($user->email)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must be logged in with a valid email address to reply.'
                    ], 401);
                }
                return redirect()->back()
                    ->with('error', 'You must be logged in with a valid email address to reply.')
                    ->withInput();
            }

            // Verify media and parent comment exist
            $media = Media::findOrFail($media_id);
            $parentComment = Comment::findOrFail($parent_id);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->media_id != $media_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The parent comment does not belong to the specified media.'
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'The parent comment does not belong to the specified media.')
                    ->withInput();
            }

            // Create the reply
            $reply = Comment::create([
                'user_id' => $user->id,
                'media_id' => $media_id,
                'parent_id' => $parent_id,
                'content' => $request->content,
            ]);

            if ($request->expectsJson()) {
                $reply = $reply->fresh(['user']);
                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                    'message' => 'Reply added successfully.'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Reply added successfully.');

        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media or parent comment not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Media or parent comment not found.')
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while adding the reply.'
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while adding the reply.')
                ->withInput();
        }
    }

    public function showMediaComments(Request $request, $media_id)
    {
        try {
            // Validate the media_id
            $validator = Validator::make(['media_id' => $media_id], [
                'media_id' => 'required|exists:media,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validate the user_id if provided
            $user_id = $request->query('user_id');
            if ($user_id) {
                $userValidator = Validator::make(['user_id' => $user_id], [
                    'user_id' => 'exists:users,id',
                ]);

                if ($userValidator->fails()) {
                    return redirect()->back()
                        ->withErrors($userValidator)
                        ->withInput();
                }
            }

            // Fetch parent comments with their replies and user data - ORDER BY DESC for newest first
            $query = Comment::where('media_id', $media_id)
                ->whereNull('parent_id')
                ->with(['replies' => function ($query) {
                    $query->orderBy('created_at', 'desc')
                          ->with('user');
                }, 'user'])
                ->orderBy('created_at', 'desc');

            // Filter by user_id if provided
            if ($user_id) {
                $query->where('user_id', $user_id);
            }

            $comments = $query->get();

            return view('comments.media', [
                'comments' => $comments,
                'media_id' => $media_id,
                'user_id' => $user_id
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while retrieving comments: ' . $e->getMessage());
        }
    }

    public function showArticleComments(Request $request, $article_id)
    {
        try {
            // Validate the article_id
            $validator = Validator::make(['article_id' => $article_id], [
                'article_id' => 'required|exists:articles,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validate the user_id if provided
            $user_id = $request->query('user_id');
            if ($user_id) {
                $userValidator = Validator::make(['user_id' => $user_id], [
                    'user_id' => 'exists:users,id',
                ]);

                if ($userValidator->fails()) {
                    return redirect()->back()
                        ->withErrors($userValidator)
                        ->withInput();
                }
            }

            // Fetch parent comments with their replies and user data - ORDER BY DESC for newest first
            $query = Comment::where('article_id', $article_id)
                ->whereNull('parent_id')
                ->with(['replies' => function ($query) {
                    $query->orderBy('created_at', 'desc')
                          ->with('user');
                }, 'user'])
                ->orderBy('created_at', 'desc');

            // Filter by user_id if provided
            if ($user_id) {
                $query->where('user_id', $user_id);
            }

            $comments = $query->get();

            return view('comments.article', [
                'comments' => $comments,
                'article_id' => $article_id,
                'user_id' => $user_id
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while retrieving comments: ' . $e->getMessage());
        }
    }

    public function deleteComment(Request $request, $comment_id)
    {
        try {
            // Validate the comment_id
            $validator = Validator::make(['comment_id' => $comment_id], [
                'comment_id' => 'required|exists:comments,id',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                        'message' => 'Validation failed.'
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            // Find the comment
            $comment = Comment::findOrFail($comment_id);

            // Check if the authenticated user is the owner of the comment
            if (auth()->user()->id !== $comment->user_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to delete this comment.'
                    ], 403);
                }
                return redirect()->back()
                    ->with('error', 'You do not have permission to delete this comment.');
            }

            // Delete the comment and all its replies
            $comment->replies()->delete();
            $comment->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comment_id' => $comment_id,
                    'message' => 'Comment deleted successfully.'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Comment deleted successfully.');

        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Comment not found.')
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while deleting the comment: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while deleting the comment: ' . $e->getMessage());
        }
    }

    // New methods for AJAX HTML rendering
    public function getCommentHtml($comment_id)
    {
        try {
            $comment = Comment::with(['user', 'replies.user'])->findOrFail($comment_id);
            
            // Return only the comment HTML without the full page layout
            return view('components.comment-item', [
                'comment' => $comment,
                'enableReplies' => true,
                'enableLikes' => true,
                'enableDelete' => true,
                'commentRoute' => 'comments.add',
                'replyRoute' => 'comments.reply',
                'likeAddRoute' => 'comments.like.add',
                'likeRemoveRoute' => 'comments.like.remove',
                'deleteRoute' => 'comments.delete',
            ])->render();
            
        } catch (\Exception $e) {
            return response('Comment not found', 404);
        }
    }

    public function getReplyHtml($reply_id)
    {
        try {
            $reply = Comment::with('user')->findOrFail($reply_id);
            
            // Return only the reply HTML without the full page layout
            return view('components.reply-item', [
                'reply' => $reply,
                'enableLikes' => true,
                'enableDelete' => true,
                'likeAddRoute' => 'comments.like.add',
                'likeRemoveRoute' => 'comments.like.remove',
                'deleteRoute' => 'comments.delete',
            ])->render();
            
        } catch (\Exception $e) {
            return response('Reply not found', 404);
        }
    }
}