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
use App\Models\SubCategory;
use getID3;
use App\Services\Videos\GoogleDriveServiceVideo; // Make sure this service is built
use App\Services\Videos\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceImage; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceThumbnail; // Make sure this service is built
class MediaController extends Controller
{
    protected $client;
    protected $driveServiceVideo;
    protected $driveServiceImage;
    protected $driveServicePDF;
    protected $driveServiceThumbnail;

    public function __construct(
        GoogleDriveServiceVideo $driveServiceVideo,
        GoogleDriveServicePDF $driveServicePDF,
        GoogleDriveServiceImage $driveServiceImage,
        GoogleDriveServiceThumbnail $driveServiceThumbnail
    ) {


        $this->driveServiceVideo = $driveServiceVideo;
        $this->client = $this->driveServiceVideo->getClient(); // Ensure this method exists in the service

        $this->driveServicePDF = $driveServicePDF;
        $this->client = $this->driveServicePDF->getClient(); // Ensure this method exists in the service

        $this->driveServiceImage = $driveServiceImage;
        $this->client = $this->driveServiceImage->getClient(); // Ensure this method exists in the service

        $this->driveServiceThumbnail = $driveServiceThumbnail;
        $this->client = $this->driveServiceThumbnail->getClient(); // Ensure this method exists in the service
    }
    public function show()
    {
        try {
            $categories = Category::with(['media' => function ($query) {
                $query->withCount('comments', 'likes');
            }])->get();

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

        try {
            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year'],
                    'user_id' => $validated['user_id']
                ],
                [
                    'description' => "Category for year {$validated['year']}"
                ]
            );

            // Find or create subcategory (month)
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => $validated['month'],
                    'category_id' => $category->id
                ],
                [
                    'description' => "Subcategory for {$validated['month']} {$validated['year']}"
                ]
            );

            $getID3 = new getID3();
            $duration = null;

            // Get video duration
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
            }


            $video = null;
            if ($request->hasFile('file')) {
                $driveService = new GoogleDriveServiceVideo();
                if ($request->file('file')->isValid()) {
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    $url = $driveService->uploadFile($request->file('file'), $filename);
                    $video = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveServicePDF = new GoogleDriveServicePDF();
                if ($request->file('pdf')->isValid()) {
                    $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                    $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                    $pdf = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            // Store thumbnail if exists
            $thumbnailPath = null;

            if ($request->hasFile('thumbnail_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('thumbnail_path')->isValid()) {
                    $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                    $thumbnailPath =  'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }

            $imagePath = null;

            if ($request->hasFile('image_path')) {
                $driveServiceImage = new GoogleDriveServiceImage();
                if ($request->file('image_path')->isValid()) {
                    $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                    $url = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                    $imagePath =  'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }


            // Save to database
            $media = Media::create([
                'user_id' => $validated['user_id'],
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'status' => 'pending', // Default status
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'duration' => $duration, // Save duration

            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_success',
                'description' => 'Uploaded media: ' . $media->title,
            ]);

            return response()->json([
                'message' => 'Media uploaded successfully.' . ' (Duration: ' . ($duration ? round($duration, 2) : 'N/A') . ' seconds)',
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
            $contents = Category::with(['media' => function ($query) {
                $query->withCount('comments', 'likes');
            }])->orderBy('created_at', 'desc')->take(10)->get();
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
            $contents = Media::with(['category'])
                ->withCount(['comments', 'likes'])
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

    public function update(Request $request, $id)
    {
        try {
            $media = Media::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year'],
                    'user_id' => $validated['user_id']
                ],
                [
                    'description' => "Category for year {$validated['year']}"
                ]
            );

            // Find or create subcategory (month)
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => $validated['month'],
                    'category_id' => $category->id
                ],
                [
                    'description' => "Subcategory for {$validated['month']} {$validated['year']}"
                ]
            );

            // Clean up mentions array
            $mentions = collect($request->input('mention', []))
                ->filter()
                ->map(fn($item) => trim($item))
                ->values()
                ->toArray();

            $getID3 = new getID3();
            $duration = $media->duration;

            // Update video duration if new file is uploaded
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $videoPath = $request->file('file')->getRealPath();
                $fileInfo = $getID3->analyze($videoPath);
                $duration = isset($fileInfo['playtime_seconds']) ? floatval($fileInfo['playtime_seconds']) : null;
            }

            // Update video file on Google Drive
            $video = $media->file_path;
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $driveServiceVideo = new GoogleDriveServiceVideo();
                // Delete old file from Google Drive if exists
                if ($media->file_path) {
                    $fileId = $driveServiceVideo->getFileIdFromUrl($media->file_path);
                    if ($fileId) {
                        $driveServiceVideo->deleteFile($fileId);
                    }
                }
                // Upload new file
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $video = $driveServiceVideo->uploadFile($request->file('file'), $filename);
                $video = 'https://drive.google.com/file/d/' . $video . '/preview';
            }

            // Update PDF file on Google Drive
            $pdf = $media->pdf;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                $driveServicePDF = new GoogleDriveServicePDF();
                // Delete old PDF from Google Drive if exists
                if ($media->pdf) {
                    $fileId = $driveServicePDF->getFileIdFromUrl($media->pdf);
                    if ($fileId) {
                        $driveServicePDF->deleteFile($fileId);
                    }
                }
                // Upload new PDF

                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $pdf = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                $pdf = 'https://drive.google.com/file/d/' . $pdf . '/preview';
            }

            // Update thumbnail if exists
            $thumbnailPath = $media->thumbnail_path;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                // Delete old thumbnail from Google Drive if exists
                if ($media->thumbnail_path) {
                    $fileId = $driveServiceThumbnail->getFileIdFromUrl($media->thumbnail_path);
                    if ($fileId) {
                        $driveServiceThumbnail->deleteFile($fileId);
                    }
                }
                // Upload new thumbnail
                $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                $thumbnailPath = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                $thumbnailPath = 'https://lh3.googleusercontent.com/d/' . $thumbnailPath . '=w1000?authuser=0';
            }

            // Update image if exists
            $imagePath = $media->image_path;
            if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
                $driveServiceImage = new GoogleDriveServiceImage();
                // Delete old image from Google Drive if exists
                if ($media->image_path) {
                    $fileId = $driveServiceImage->getFileIdFromUrl($media->image_path);
                    if ($fileId) {
                        $driveServiceImage->deleteFile($fileId);
                    }
                }
                // Upload new image
                $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                $imagePath = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                $imagePath = 'https://lh3.googleusercontent.com/d/' . $imagePath . '=w1000?authuser=0';
            }

            // Update database
            $media->update([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'mentions' => json_encode($mentions),
                'duration' => $duration,
            ]);

            // Log success
            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'media_update_success',
                'description' => 'Updated media: ' . $media->title,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Media updated successfully.',
                'data' => $media
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Media update error: ' . $e->getMessage());

            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'media_update_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Media update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
