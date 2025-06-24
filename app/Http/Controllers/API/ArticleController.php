<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use Illuminate\Http\Request;
use App\Services\Article\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Article\GoogleDriveServiceThumbnail; // Make sure this service is built
class ArticleController extends Controller
{
    protected $client;
    protected $driveServicePDF;
    protected $driveServiceThumbnail;
    public function __construct(
        GoogleDriveServicePDF $driveServicePDF,
        GoogleDriveServiceThumbnail $driveServiceThumbnail
    ) {
        $this->driveServiceThumbnail = $driveServiceThumbnail;
        $this->driveServiceThumbnail = $this->driveServiceThumbnail->getClient();

        $this->driveServicePDF = $driveServicePDF;
        $this->client = $this->driveServicePDF->getClient(); // Ensure this method exists in the service
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
            $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveServicePDF = new GoogleDriveServicePDF();
                if ($request->file('pdf')->isValid()) {
                    $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                    $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                    $url = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';

                    $pdf = $url;
                }
            }

            // Store thumbnail if exists
            $image_path = null;

            if ($request->hasFile('image_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('image_path')->isValid()) {
                    $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('image_path'), $filename);
                    $url = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';

                    $image_path = $url;
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
                'pdf' => $pdf,
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
