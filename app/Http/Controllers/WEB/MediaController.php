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
        return view('pages.content.show', compact('media'));
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
                'user_id' => Auth::id(),
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
