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
use App\Services\GoogleDriveService; // Make sure this service is built
use getID3;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    protected $client;
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
        $this->client = $this->driveService->getClient(); // Ensure this method exists in the service
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

    public function getone($id)
    {
        $media = Media::with(['category', 'comments'])->findOrFail($id);
        return view('pages.content.single_video', compact('media'));
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
            if ($request->hasFile('thumbnail_path')) {
                $thumbnailPath = $request->file('thumbnail_path')->store('thumbnail_path', 'public');
            }
             $imagePath = null;
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('image_path', 'public');
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
                // Delete old file from Google Drive if exists
                if ($media->file_path) {
                    $fileId = $this->driveService->getFileIdFromUrl($media->file_path);
                    if ($fileId) {
                        $this->driveService->deleteFile($fileId);
                    }
                }
                // Upload new file
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $video = $this->driveService->uploadFile($request->file('file'), $filename);
            }

            // Update PDF file on Google Drive
            $pdf = $media->pdf;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                // Delete old PDF from Google Drive if exists
                if ($media->pdf) {
                    $fileId = $this->driveService->getFileIdFromUrl($media->pdf);
                    if ($fileId) {
                        $this->driveService->deleteFile($fileId);
                    }
                }
                // Upload new PDF
                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $pdf = $this->driveService->uploadFile($request->file('pdf'), $filename);
            }

            // Update thumbnail if exists
            $thumbnailPath = $media->thumbnail_path;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                // Delete old thumbnail from storage if exists
                if ($media->thumbnail_path) {
                    $oldPath = str_replace('http://127.0.0.1:8000/', '', $media->thumbnail_path);
                    Storage::disk('public')->delete($oldPath);
                }
                $thumbnailPath = 'http://127.0.0.1:8000/' . $request->file('thumbnail_path')->store('thumbnails', 'public');
            }

            // Update image if exists
            $imagePath = $media->image_path;
            if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
                // Delete old image from storage if exists
                if ($media->image_path) {
                    $oldPath = str_replace('http://127.0.0.1:8000/', '', $media->image_path);
                    Storage::disk('public')->delete($oldPath);
                }
                $imagePath = 'http://127.0.0.1:8000/' . $request->file('image_path')->store('images', 'public');
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
}
