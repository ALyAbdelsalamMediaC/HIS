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
use App\Models\User;
use App\Services\Videos\GoogleDriveServiceVideo; // Make sure this service is built
use App\Services\Videos\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceImage; // Make sure this service is built
use App\Services\Videos\GoogleDriveServiceThumbnail; // Make sure this service is built
use getID3;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    protected $client;
    protected $driveServiceVideo;
    protected $driveServicePDF;
    protected $driveServiceImage;
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

    public function getall(Request $request)
    {
        try {

            $categories = Category::all();

            if ($this->client->isAccessTokenExpired()) {
                return redirect('http://localhost:8000/get-google-token.php?redirect=' . urlencode(url()->current()));
            } else {

                $query = Media::with('category');

                // Search by title
                if ($request->filled('title')) {
                    $query->where('title', 'like', '%' . $request->input('title') . '%');
                }

                // Filter by category name
                if ($request->filled('category')) {
                    $query->whereHas('category', function ($q) use ($request) {
                        $q->where('name', $request->input('category'));
                    });
                }

                // Filter by status
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }

                // Filter by date (created_at)
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->input('date_to'));
                }

                // Order by latest
                $media = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

                // Get all users with role 'reviewer'
                $reviewers = User::where('role', 'reviewer')->get();

                return view('pages.content.videos', compact('media', 'reviewers', 'categories'));
            }
        } catch (Exception $e) {
            LaravelLog::error('Media getall error: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch media.');
        }
    }
    public function assignTo(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'reviewer_ids' => 'required|array|min:1',
                'reviewer_ids.*' => 'exists:users,id'
            ]);

            // Get reviewers from request body
            $reviewersArray = $request->input('reviewer_ids', []);

            // If it's a string, convert to array
            if (is_string($reviewersArray)) {
                $reviewersArray = explode(',', $reviewersArray);
            }

            // Clean up any whitespace and filter out empty values
            $reviewersArray = array_filter(array_map('trim', $reviewersArray));

            // Convert to JSON
            $reviewersJson = json_encode($reviewersArray);

            // Update media table using Eloquent
            Media::where('id', $id)
                ->update(['assigned_to' => $reviewersJson]);

            return back()->with('success', 'Reviewers assigned successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Assign to error: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign reviewers.');
        }
    }
    public function recently_Added()
    {
        try {
            $contents = Media::with('category')->orderBy('created_at', 'desc')->take(10)->get();
            return view('pages.content.recently_added', compact('contents'));
        } catch (Exception $e) {
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch recently added media.');
        }
    }

    public function getone($id, $status)
    {
        $media = Media::with(['category', 'comments'])->findOrFail($id);
        $user = Auth::user();

        if ($status === 'pending') {
            if ($user && $user->role === 'admin') {
                return view('pages.content.video.single_video_pending_admin', compact('media'));
            } elseif ($user && $user->role === 'reviewer') {
                return view('pages.content.video.single_video_pending_reviewer', compact('media'));
            }
        } elseif ($status === 'published') {
            return view('pages.content.video.single_video', compact('media'));
        }

        // Default fallback
        return back()->with('error', 'Invalid status or permissions.');
    }
    public function validation()
    {
        $categories = Category::all();

        if ($this->client->isAccessTokenExpired()) {
            return redirect('http://localhost:8000/get-google-token.php?redirect=' . urlencode(url()->current()));
        } else {
            return view('pages.content.add_video', compact('categories'));
        }
    }

    public function create()
    {
        $categories = Category::all();
        return view('pages.content.add_video', compact('categories'));
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
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
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
                    $thumbnailPath = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            $imagePath = null;

            if ($request->hasFile('image_path')) {
                $driveServiceImage = new GoogleDriveServiceImage();
                if ($request->file('image_path')->isValid()) {
                    $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                    $url = $driveServiceImage->uploadImage($request->file('image_path'), $filename);
                    $imagePath = 'https://drive.google.com/file/d/' . $url . '/preview';
                }
            }

            // Save to database
            $media = Media::create([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'user_id' => Auth::id(),
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'status' => 'published',
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
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

            return redirect()->route('content.videos')->with('success', 'Media uploaded successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Media upload error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_upload_error',
                'description' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Media upload failed. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $media = Media::with('category')->findOrFail($id);
            $categories = Category::all();

            return view('pages.content.edit_video', compact('media', 'categories'));
        } catch (Exception $e) {
            LaravelLog::error('Media edit error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load media for editing.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $media = Media::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:mp4,avi,mov|max:51200', // 50MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_recommended' => 'nullable|boolean',
            ]);

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
                $thumbnailPath = 'https://drive.google.com/file/d/' . $thumbnailPath . '/preview';
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
                $imagePath = 'https://drive.google.com/file/d/' . $imagePath . '/preview';
            }

            // Update database
            $media->update([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $video,
                'pdf' => $pdf,
                'thumbnail_path' => $thumbnailPath,
                'image_path' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'is_recommended' => $request->boolean('is_recommended'),
                'duration' => $duration,
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_update_success',
                'description' => 'Updated media: ' . $media->title,
            ]);

            return redirect()->route('content.videos')->with('success', 'Media updated successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Media update error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_update_error',
                'description' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Media update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $media = Media::findOrFail($id);

            // Delete video file from Google Drive if exists
            if ($media->file_path) {
                $fileId = $this->driveServiceVideo->getFileIdFromUrl($media->file_path);
                if ($fileId) {
                    $this->driveServiceVideo->deleteFile($fileId);
                }
            }

            // Delete PDF file from Google Drive if exists
            if ($media->pdf) {
                $fileId = $this->driveServicePDF->getFileIdFromUrl($media->pdf);
                if ($fileId) {
                    $this->driveServicePDF->deleteFile($fileId);
                }
            }

            // Delete thumbnail from Google Drive if exists
            if ($media->thumbnail_path) {
                $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($media->thumbnail_path);
                if ($fileId) {
                    $this->driveServiceThumbnail->deleteFile($fileId);
                }
            }

            // Delete image from Google Drive if exists
            if ($media->image_path) {
                $fileId = $this->driveServiceImage->getFileIdFromUrl($media->image_path);
                if ($fileId) {
                    $this->driveServiceImage->deleteFile($fileId);
                }
            }

            // Log the deletion
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_delete_success',
                'description' => 'Deleted media: ' . $media->title,
            ]);

            // Delete the media record from the database
            $media->delete();

            return redirect()->route('content.videos')->with('success', 'Media deleted successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Media delete error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'media_delete_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete media: ' . $e->getMessage());
        }
    }

    public function stream($id)
    {
        try {
            $media = Media::findOrFail($id);
            $filePath = $media->file_path;

            // Extract Google Drive file ID from the URL
            $fileId = null;
            if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $filePath, $matches)) {
                $fileId = $matches[1];
            }

            if (!$fileId) {
                abort(404, 'Google Drive file ID not found.');
            }

            $driveService = new \Google\Service\Drive($this->client);
            $file = $driveService->files->get($fileId, ['alt' => 'media']);

            $response = new StreamedResponse(function () use ($file) {
                $chunkSize = 1024 * 1024; // 1MB chunks
                $stream = $file->getBody();
                while (!$stream->eof()) {
                    echo $stream->read($chunkSize);
                    flush();
                }
            });

            $response->headers->set('Content-Type', 'video/mp4');
            $response->headers->set('Content-Length', $file->getHeaderLine('Content-Length'));
            $response->headers->set('Accept-Ranges', 'bytes');

            return $response;

        } catch (Exception $e) {
            LaravelLog::error('Media streaming error: ' . $e->getMessage());
            abort(500, 'Error streaming video.');
        }
    }
}
