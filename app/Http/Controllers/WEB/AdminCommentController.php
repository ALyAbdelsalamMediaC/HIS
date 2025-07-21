<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use App\Models\AdminComment;
use App\Models\Media;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AdminCommentController extends Controller
{
    public function addComment(Request $request, $media_id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get the authenticated user
            $user = auth()->user();
            if (!$user || empty($user->email)) {
                if ($request->wantsJson()) {
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
                if ($request->wantsJson()) {
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
            $comment = AdminComment::create([
                'user_id' => $user->id,
                'media_id' => $media_id,
                'parent_id' => null,
                'content' => $request->content,
            ]);

            // Load user relationship for JSON response
            $comment->load('user');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment added successfully.',
                    'comment' => $comment
                ]);
            }

            return redirect()->back()
                ->with('success', 'Comment added successfully.');

        } catch (ModelNotFoundException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Media not found.')
                ->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
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
        return view('pages.content.video.single_video_inreview_admin', compact('media_id', 'parent_id'));
    }

    public function reply(Request $request, $media_id, $parent_id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get the authenticated user
            $user = auth()->user();
            if (!$user || empty($user->email)) {
                if ($request->wantsJson()) {
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
            $parentComment = AdminComment::findOrFail($parent_id);

            // Verify that parent comment belongs to the specified media
            if ($parentComment->media_id != $media_id) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent comment for this media.'
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'Invalid parent comment for this media.')
                    ->withInput();
            }

            // Create the reply
            $comment = AdminComment::create([
                'user_id' => $user->id,
                'media_id' => $media_id,
                'parent_id' => $parent_id,
                'content' => $request->content,
            ]);

            // Load user relationship for JSON response
            $comment->load('user');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reply added successfully.',
                    'comment' => $comment
                ]);
            }

            return redirect()->back()
                ->with('success', 'Reply added successfully.');

        } catch (ModelNotFoundException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media or parent comment not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Media or parent comment not found.')
                ->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
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
            $query = AdminComment::where('media_id', $media_id)
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

    public function deleteComment(Request $request, $comment_id)
    {
        try {
            // Validate the comment_id
            $validator = Validator::make(['comment_id' => $comment_id], [
                'comment_id' => 'required|exists:admin_comments,id',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Find the comment
            $comment = AdminComment::findOrFail($comment_id);

            // Check if the authenticated user is the owner of the comment
            if (auth()->user()->role !== 'admin' && auth()->user()->id !== $comment->user_id){
                return redirect()->back()
                    ->with('error', 'You do not have permission to delete this comment.');
            }

            // Delete the comment and all its replies
            $comment->replies()->delete();
            $comment->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment deleted successfully.'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Comment deleted successfully.');

        } catch (ModelNotFoundException $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found.'
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Comment not found.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while deleting the comment.'
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while deleting the comment.');
        }
    }

    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media_id' => 'required|exists:media,id',
            'user_id' => 'required|exists:users,id',
            'rate' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
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

        $user = User::find($request->user_id);
        if (!$user || $user->role !== 'admin') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can submit a rating.'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Only admins can submit a rating.')
                ->withInput();
        }

        Rate::updateOrCreate(
            [
                'media_id' => $request->media_id,
                'user_id' => $request->user_id,
            ],
            [
                'rate' => $request->rate,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully.'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Rating submitted successfully.');
    }

    // AJAX HTML rendering for admin comments
    public function getCommentHtml($comment_id)
    {
        try {
            $comment = AdminComment::with('user')->findOrFail($comment_id);
            // Return only the comment HTML without the full page layout
            return view('components.comment-item', [
                'comment' => $comment,
                'enableReplies' => false,
                'enableLikes' => false,
                'enableDelete' => true,
                'commentRoute' => 'AdminComment.add',
                'deleteRoute' => 'AdminComment.delete',
            ])->render();
        } catch (\Exception $e) {
            return response('Comment not found', 404);
        }
    }
}