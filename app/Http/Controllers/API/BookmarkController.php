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
    /**
     * Add a bookmark for an article or media
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_id' => 'nullable|integer|exists:articles,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'flag' => 'required|string|max:255',
        ], [
            'article_id.exists' => 'The specified article does not exist.',
            'media_id.exists' => 'The specified media does not exist.',
        ]);

        // Ensure exactly one of article_id or media_id is provided
        if (!$request->has('article_id') && !$request->has('media_id') || $request->has('article_id') && $request->has('media_id')) {
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

        $user = Auth::user();
        $articleId = $request->input('article_id');
        $mediaId = $request->input('media_id');
        $flag = $request->input('flag');

        // Check if bookmark already exists
        $existingBookmark = Bookmark::where('user_id', $user->id)
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
            ], 409);
        }

        // Create new bookmark
        $bookmark = new Bookmark();
        $bookmark->user_id = $user->id;
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

    /**
     * Remove a bookmark
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_id' => 'nullable|integer|exists:articles,id',
            'media_id' => 'nullable|integer|exists:media,id',
        ], [
            'article_id.exists' => 'The specified article does not exist.',
            'media_id.exists' => 'The specified media does not exist.',
        ]);

        // Ensure exactly one of article_id or media_id is provided
        if (!$request->has('article_id') && !$request->has('media_id') || $request->has('article_id') && $request->has('media_id')) {
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

        $user = Auth::user();
        $articleId = $request->input('article_id');
        $mediaId = $request->input('media_id');

        $bookmark = Bookmark::where('user_id', $user->id)
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

    /**
     * Get all bookmarks for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookmarks(Request $request)
    {
        $user = Auth::user();
        
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['article' => function ($query) {
                $query->select('id', 'title', 'description', 'image_path', 'thumbnail_path');
            }, 'media' => function ($query) {
                $query->select('id', 'title', 'description', 'file_path', 'thumbnail_path', 'image_path');
            }])
            ->get()
            ->map(function ($bookmark) {
                $item = $bookmark->article ?? $bookmark->media;
                return [
                    'id' => $bookmark->id,
                    'article_id' => $bookmark->article_id,
                    'media_id' => $bookmark->media_id,
                    'flag' => $bookmark->flag,
                    'item' => $item,
                    'created_at' => $bookmark->created_at,
                    'updated_at' => $bookmark->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $bookmarks
        ], 200);
    }
}