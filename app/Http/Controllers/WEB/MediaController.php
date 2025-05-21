<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Services\GoogleDriveService; // Make sure this service is built

class MediaController extends Controller
{
    public function show(){
        return view('media.upload');
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'media_type' => 'required|in:video,image',
                'file' => 'required|file|mimes:mp4,avi,mov,jpeg,png,jpg|max:51200', // 50MB limit
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_recommended' => 'nullable|boolean',
            ]);

              $video = null;

            if ($request->hasFile('file')) {
                $driveService = new GoogleDriveService();
                // foreach ($request->file('file') as $videoFile) {
                    if ($request->file('file')->isValid()) {
                        $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                        $url = $driveService->uploadFile($request->file('file'), $filename);
                        $video = $url;
                    }
                // }
                // $updatedUrl = implode(',', $video);
            }

            // Store file
            // $filePath = $request->file('file')->store('media', 'public');

            // Store thumbnail if exists
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            }

            // Save to database
            $media = Media::create([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'media_type' => $validated['media_type'],
                'file_path' => $video,
                'thumbnail_path' => $thumbnailPath,
                'is_featured' => $request->boolean('is_featured'),
                'is_recommended' => $request->boolean('is_recommended'),
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_success',
                'description' => 'Uploaded media: ' . $media->title,
            ]);

            return response()->json([
                'message' => 'Media uploaded successfully.',
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
}
