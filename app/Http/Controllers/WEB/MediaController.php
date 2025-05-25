<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Models\Category;
use App\Services\GoogleDriveService; // Make sure this service is built

class MediaController extends Controller
{
    public function getall(){
        $media = Media::with('category')->get();
        return view('media.index', compact('media'));
    }

    public function getone($id)
    {
        $media = Media::with(['category', 'comments'])->findOrFail($id);
        return view('media.show', compact('media'));
        

    }
    
    
    public function create()
    {
        $categories = Category::all();
        return view('media.upload', compact('categories'));
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'required|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_recommended' => 'nullable|boolean',
            ]);

              $video = null;
            if ($request->hasFile('file')) {
                $driveService = new GoogleDriveService();
                    if ($request->file('file')->isValid()) {
                        $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                        $url = $driveService->uploadFile($request->file('file'), $filename);
                        $video = $url;
                    }
                
            }

              $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveService = new GoogleDriveService();
                    if ($request->file('pdf')->isValid()) {
                        $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                        $url = $driveService->uploadFile($request->file('pdf'), $filename);
                        $pdf = $url;
                    }
                
            }

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
                'file_path' => $video,
                'pdf' => $pdf,
                'status' => 'approved',
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
