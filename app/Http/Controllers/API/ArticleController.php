<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use Illuminate\Http\Request;
use App\Services\GoogleDriveService; // Make sure this service is built

class ArticleController extends Controller
{
      protected $client;
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
        $this->client = $this->driveService->getClient(); // Ensure this method exists in the service
    }
    public function show()
    {
        try {
            $Article = Article::all();

            return response()->json([
                'success' => true,
                'message' => 'get all Articles',
                'data' => $Article
            ], 200);

        } catch (Exception $e) {
            LaravelLog::error('Media retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve media.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'is_featured' => 'nullable|boolean',
            ]);



            // Store thumbnail if exists
            $image_path = null;
            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path')->store('image_path', 'public');
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


            // Save to database
            $Article = Article::create([
                'category_id' => $validated['category_id'],
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'image_path' => $image_path,
                'pdf'=> $pdf,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_upload_success',
                'description' => 'Uploaded article: ' . $Article->title,
            ]);

             return response()->json([
                'success' => true,
                'message' => 'Article uploaded successfully.',
                'data' => $Article
            ], 200);

        } catch (Exception $e) {
            LaravelLog::error('Article upload error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_upload_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Article upload failed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
