<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentArticle;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Services\NotificationService;

class CommentsController extends Controller
{
    protected $notificationService;
    public function __construct(
        NotificationService $notificationService
    ) {

        $this->notificationService = $notificationService;
    }
    public function addComment(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'media_id' => 'required|exists:media,id',
                'parent_id' => 'nullable|exists:comments,id',
                'content' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check if the user has a valid email
            $user = User::findOrFail($request->user_id);
            if (empty($user->email)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The specified user does not have a valid email address.',
                ], 422);
            }

            // Create the comment
            $comment = Comment::create([
                'user_id' => $request->user_id,
                'media_id' => $request->media_id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);

            $sender = User::find($request->user_id);
            $user_media = Media::where('id', $request->media_id)->with('user')->first();
            $receiver = $user_media->user;
            $title = "New comment on media id: " . $request->media_id;
            $body = "content: " . $request->content;
            $route = "content/videos/" . $request->media_id . "/" . $user_media->status;

            $this->notificationService->sendNotification(
                $sender,
                $receiver,
                $title,
                $body,
                $route,
                $request->media_id
            );

            // Load user data
            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'message' => 'Comment created successfully.',
                'data' => $comment,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User, media, or parent comment not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while creating the comment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reply(Request $request)
{
    try {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'media_id' => 'required|exists:media,id',
            'parent_id' => 'required|exists:comments,id',
            'content' => 'required|string|min:1|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if the user has a valid email
        $user = User::findOrFail($request->user_id);
        if (empty($user->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The specified user does not have a valid email address.',
            ], 422);
        }

        // Verify that parent comment and media_id are consistent
        $parentComment = Comment::findOrFail($request->parent_id);
        if ($parentComment->media_id != $request->media_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'The parent comment does not belong to the specified media.',
            ], 422);
        }

        // Create the reply
        $reply = Comment::create([
            'user_id' => $request->user_id,
            'media_id' => $request->media_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Load user data
        $reply->load('user');

        // Notify the media author
        $sender = User::find($request->user_id);
        $user_media = Media::where('id', $request->media_id)->with('user')->first();
        $media_author = $user_media->user;
        $title = "New reply on your media id: " . $request->media_id;
        $body = "Content: " . $request->content;
        $route = "content/videos/" . $request->media_id . "/" . $user_media->status;

        if ($media_author->id != $sender->id) { // Avoid sending notification to the same user
            $this->notificationService->sendNotification(
                $sender,
                $media_author,
                $title,
                $body,
                $route,
                $request->media_id
            );
        }

        // Notify the parent comment author
        $parent_comment_author = User::find($parentComment->user_id);
        if ($parent_comment_author && $parent_comment_author->id != $sender->id && $parent_comment_author->id != $media_author->id) {
            $title = "New reply to your comment on media id: " . $request->media_id;
            $this->notificationService->sendNotification(
                $sender,
                $parent_comment_author,
                $title,
                $body,
                $route,
                $request->media_id
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reply created successfully.',
            'data' => $reply,
        ], 201);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'User, media, or parent comment not found.',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred while creating the reply.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    public function getCommentsByMediaId(Request $request)
    {
        try {

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'media_id' => 'required|exists:media,id'
            ], [
                'user_id.exists' => 'The specified user does not exist.',
                'media_id' => 'required|exists:media,id',
            ]);
            // Validate the user_id if provided
            $user_id = (int) $validated['user_id'];
            $media_id = (int) $validated['media_id'];

            $query = Comment::where('media_id', $media_id)
                ->whereNull('parent_id')
                ->with([
                    'replies' => function ($query) use ($user_id) {
                        $query->orderBy('created_at', 'asc')
                            ->with(['user']);
                    },
                    'user'
                ])
                ->orderBy('created_at', 'asc');

            if ($user_id) {
                // No filter by user_id, get all comments for the media
            }

            // Get comments
            $comments = $query->get();

            // Attach is_liked attribute for each comment and its replies
            $comments->each(function ($comment) use ($user_id) {
                $comment->is_liked = $comment->likes()->where('user_id', $user_id)->exists();
                if ($comment->relationLoaded('replies')) {
                    $comment->replies->each(function ($reply) use ($user_id) {
                        $reply->is_liked = $reply->likes()->where('user_id', $user_id)->exists();
                    });
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Comments retrieved successfully.',
                'data' => $comments,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving comments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCommentsByArticleId(Request $request)
    {
        try {
            // Validate the article_id
            $validator = Validator::make(['article_id' => $request->article_id], [
                'article_id' => 'required|exists:articles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate the user_id if provided
            $user_id = $request->user_id;
            if ($user_id) {
                $userValidator = Validator::make(['user_id' => $user_id], [
                    'user_id' => 'exists:users,id',
                ]);

                if ($userValidator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $userValidator->errors(),
                    ], 422);
                }
            }

            // Fetch parent comments with their replies and user data
            $query = Comment::where('article_id', $request->article_id)
                ->whereNull('parent_id')
                ->with(['replies' => function ($query) {
                    $query->orderBy('created_at', 'asc')
                        ->with('user');
                }, 'user'])
                ->orderBy('created_at', 'asc');

            // Filter by user_id if provided
            if ($user_id) {
                $query->where('user_id', $user_id);
            }

            $comments = $query->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Comments retrieved successfully.',
                'data' => $comments,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving comments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteComment(Request $request)
    {
        try {
            // Validate the comment ID
            $comment = Comment::findOrFail($request->comment_id);

            // Check if the user is authorized to delete the comment
            if ($comment->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this comment.',
                ], 403);
            }

            // Delete the comment
            $comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comment not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while deleting the comment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
