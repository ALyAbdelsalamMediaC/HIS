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
use App\Models\Like;
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
                return response()->json([
                    'success' => false,
                    'message' => 'You have already liked this media.'
                ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Media liked successfully.',
                'like_id' => $like->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Like addition failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to like media: ' . $e->getMessage()
            ], 500);
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

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not liked this media.'
                ], 404);
            }

            // Get media title before deleting like for logging
            $media = Media::findOrFail($mediaId);

            // Delete the like
            $like->delete();

            // Log the action
            Log::create([
                'user_id' => Auth::id(),
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
}
