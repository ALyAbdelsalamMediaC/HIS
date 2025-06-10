<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Models\Category;
use getID3;

class MediaController extends Controller
{
    public function show()
    {
        try {
            $categories = Category::with('media')->get();

            return response()->json([
                'success' => true,
                'message' => 'Media categories retrieved successfully.',
                'data' => $categories
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Media retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }
    public function store(Request $request)
    {

        // Get the original file name
        $originalName = $request->file('file')->getClientOriginalName();
        var_dump($originalName);

        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'required|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_recommended' => 'nullable|boolean',
            ]);

            $getID3 = new getID3();
            $duration = null;

            // Get video duration
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
            }


            // Store thumbnail if exists
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            }

            $videoPath = null;
            if ($request->hasFile('file')) {
                $videoPath = $request->file('file')->store('Videos', 'public');
            }

            $pdfPath = null;
            if ($request->hasFile('pdf')) {
                $pdfPath = $request->file('pdf')->store('Pdfs', 'public');
            }


            // Save to database
            $media = Media::create([
                'user_id' => $validated['user_id'],
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $videoPath,
                'pdf' => $pdfPath,
                'thumbnail_path' => $thumbnailPath,
                'status' => 'pending', // Default status
                'is_featured' => $request->boolean('is_featured'),
                'is_recommended' => $request->boolean('is_recommended'),
                'duration' => $duration, // Save duration

            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_success',
                'description' => 'Uploaded media: ' . $media->title,
            ]);

            return response()->json([
                'message' => 'Media uploaded successfully.'. ' (Duration: ' . ($duration ? round($duration, 2) : 'N/A') . ' seconds)',
                'media' => $media
            ], 201);
        } catch (Exception $e) {
            LaravelLog::error('Media upload error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Media upload failed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function views(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'video_id' => 'required|string'
            ]);

            $video_id = $validated['video_id'];
            $media = Media::where('file_path', $video_id)->first();

            if (!$media) {
                throw new Exception('Media not found.');
            }

            $media->views += 1;
            $media->save();

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_view_success',
                'description' => 'Viewed media: ' . $media->title,
            ]);

            return response()->json($media, 200);
        } catch (Exception $e) {
            LaravelLog::error('Media view error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_view_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update media views.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function recently_Added()
    {
        try {
            $contents = Media::with('category')->orderBy('created_at', 'desc')->take(10)->get();
            return response()->json([
                'success' => true,
                'message' => 'Recently added media retrieved successfully.',
                'data' => $contents
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch recently added media.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function featured()
    {
        try {
            $contents = Media::with('category')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json([
                'success' => true,
                'message' => 'Recently added media retrieved successfully.',
                'data' => $contents
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch recently added media.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
