<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
class CommentsController extends Controller
{
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
            // Validate the media_id
            $validator = Validator::make(['media_id' => $request->media_id], [
                'media_id' => 'required|exists:media,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Fetch parent comments with their replies
            $comments = Comment::where('media_id', $request->media_id)
                ->whereNull('parent_id')
                ->with(['replies' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->orderBy('created_at', 'asc')
                ->get();

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

            // Fetch parent comments with their replies
            $comments = CommentArticle::where('article_id', $request->article_id)
                ->whereNull('parent_id')
                ->with(['replies' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->orderBy('created_at', 'asc')
                ->get();

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
}
