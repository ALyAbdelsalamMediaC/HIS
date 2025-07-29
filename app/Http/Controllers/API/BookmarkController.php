<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Bookmark;
use App\Models\Article;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookmarkController extends Controller
{

    public function addBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'article_id' => 'nullable|integer|exists:articles,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'flag' => 'required|string|max:255',
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
        $flag = $request->input('flag');
        $media = Media::where('id', $mediaId)->where('status'!='published')->first();
        if ($media) {
            return response()->json([
                'status' => 'error',
                'message' => 'media is not published'
            ], 200);
        }
        // Check if bookmark already exists
        $existingBookmark = Bookmark::where('user_id', $userId)
            ->where(function ($query) use ($articleId, $mediaId) {
                if ($articleId) {
                    $query->where('article_id', $articleId)->whereNull('media_id');
                } else {
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
        $bookmark->article_id = $articleId;
        $bookmark->media_id = $mediaId;
        $bookmark->save();

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
        $mediaBookmarks = Bookmark::with(['media' => function ($query) use ($userId) {
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
                // 'articleLikes' => $articleLikes,
            ]
        ], 200);
    }
}
