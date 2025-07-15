<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CommentArticle;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CommentArticleController extends Controller
{
     public function addComment(Request $request, $article_id)
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
            if (!Article::find($article_id)) {
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
            $comment = CommentArticle::create([
                'user_id' => $user->id,
                'article_id' => $article_id,
                'parent_id' => null,
                'content' => $request->content,
            ]);
            
            if ($request->expectsJson()) {
                $comment = $comment->fresh(['user', 'replies.user']);
                return response()->json([
                    'success' => true,
                    'comment' => $comment,
                    'message' => 'Comment added successfully.'
                ]);
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

    public function showReplyForm($article_id, $parent_id)
    {
        // Pass article_id and parent_id to the view
        return view('pages.content.article.single_article_published', compact('article_id', 'parent_id'));
    }

    public function reply(Request $request, $article_id, $parent_id)
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
            $parentComment = CommentArticle::findOrFail($parent_id);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->article_id != $article_id) {
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
            $reply = CommentArticle::create([
                'user_id' => $user->id,
                'article_id' => $article_id,
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

    public function showMediaComments(Request $request, $article_id)
    {
        try {
            // Validate the article_id
            $validator = Validator::make(['article_id' => $article_id], [
                'article_id' => 'required|exists:media,id',
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
            $query = CommentArticle::where('article_id', $article_id)
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
                'article_id' => $article_id,
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
            $query = CommentArticle::where('article_id', $article_id)
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
                'comment_id' => 'required|exists:Comment_articles,id',
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
            $comment = CommentArticle::where('id',$comment_id)->first();
            
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
}