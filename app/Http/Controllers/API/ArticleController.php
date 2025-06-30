<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Log;
use App\Models\SubCategory;
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
                'year' => 'required|digits:4',
                'month' => 'required',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'is_featured' => 'nullable|boolean',
                'is_favorite' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(
                [
                    'name' => $validated['year'],
                    'user_id' => Auth::id()
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

            // Store thumbnail if exists
            $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveServicePDF = new GoogleDriveServicePDF();
                if ($request->file('pdf')->isValid()) {
                    $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                    $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                    $url = 'https://drive.google.com/file/d/' . $url . '/preview';
                    $pdf = $url;
                }
            }

            // Store thumbnail if exists
            $thumbnail_path = null;

            if ($request->hasFile('thumbnail_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('thumbnail_path')->isValid()) {
                    $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                    $url = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';

                    $thumbnail_path = $url;
                }
            }


            // Save to database
            $Article = Article::create([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'thumbnail_path' => $thumbnail_path,
                'pdf' => $pdf,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
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

    public function update(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'is_favorite' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'mention' => 'nullable|array',
                'mention.*' => 'nullable|string|max:255',
            ]);

            // Clean up mentions array
            $mentions = collect($request->input('mention', []))
                ->filter()
                ->map(fn($item) => trim($item))
                ->values()
                ->toArray();

            
            // Update PDF file on Google Drive
            $pdf = $article->pdf;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                // Delete old PDF from Google Drive if exists
                if ($article->pdf) {
                    $fileId = $this->driveServicePDF->getFileIdFromUrl($article->pdf);
                    if ($fileId) {
                        $this->driveServicePDF->deleteFile($fileId);
                    }
                }
                // Upload new PDF
                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $pdf = $this->driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                $pdf = 'https://drive.google.com/file/d/' . $pdf . '/preview';
            }

          

            // Update thumbnail if exists
            $thumbnail_path = $article->thumbnail_path;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                // Delete old thumbnail from Google Drive if exists
                if ($article->thumbnail_path) {
                    $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($article->thumbnail_path);
                    if ($fileId) {
                        $this->driveServiceThumbnail->deleteFile($fileId);
                    }
                }
             

            // Update database
            $article->update([
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'thumbnail_path' => $thumbnail_path, // Fixed: Corrected field name
                'pdf' => $pdf,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'mention' => json_encode($mentions),
            ]);

            // Log success
            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'article_update_success',
                'description' => 'Updated article: ' . $article->title,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Article updated successfully.',
                'data' => $article
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Article update error: ' . $e->getMessage());

            Log::create([
                'user_id' => $request->input('user_id', null),
                'type' => 'article_update_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Article update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
