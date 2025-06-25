<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Services\Article\GoogleDriveServicePDF; // Make sure this service is built
use App\Services\Article\GoogleDriveServiceThumbnail; // Make sure this service is built
use Illuminate\Support\Facades\Storage;


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
        // $this->driveServiceThumbnail = $this->driveServiceThumbnail->getClient();

        $this->driveServicePDF = $driveServicePDF;
        $this->client = $this->driveServicePDF->getClient(); // Ensure this method exists in the service
    }

    public function getall(Request $request)
    {
       
        try {
            $categories = Category::all();

            $article = Article::with('category', 'CommentArticle')
                    ->withCount('CommentArticle')->orderBy('created_at', 'desc')->paginate(12)->withQueryString();;
            // Search by title
            if ($request->filled('title')) {
                $article->where('title', 'like', '%' . $request->input('title') . '%');
            }

            // Filter by category name
            if ($request->filled('category')) {
                $article->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->input('category'));
                });
            }
            return view('pages.content.articles', compact('article', 'categories'));
        } catch (Exception $e) {
            LaravelLog::error('Article getall error: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch article.');
        }
    }
    public function recently_Added()
    {
        try {
            $articles = Article::with('category')->orderBy('created_at', 'desc')->take(10)->get();
            return view('pages.article.recently_added', compact('articles'));
        } catch (Exception $e) {
            LaravelLog::error('Recently Added error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to fetch recently added article.');
        }
    }

    public function getone($id)
    {
        $article = Article::with(['category', 'comments'])->findOrFail($id);
        return view('pages.article.show', compact('article'));
    }


    public function create()
    {
        $categories = Category::all();
        $users = User::all();
        return view('pages.content.add_article', compact('categories', 'users'));
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
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

            $pdf = null;
            if ($request->hasFile('pdf')) {
                $driveServicePDF = new GoogleDriveServicePDF();
                if ($request->file('pdf')->isValid()) {
                    $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                    $url = $driveServicePDF->uploadPdf($request->file('pdf'), $filename);
                    $pdf = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }

            // Store thumbnail if exists
            $image_path = null;

            if ($request->hasFile('image_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('image_path')->isValid()) {
                    $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('image_path'), $filename);
                    $image_path = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }


            $thumbnail_path = null;

            if ($request->hasFile('thumbnail_path')) {
                $driveServiceThumbnail = new GoogleDriveServiceThumbnail();
                if ($request->file('thumbnail_path')->isValid()) {
                    $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                    $thumbnail_path = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }

            // Save to database
            $Article = Article::create([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'image_path' => $image_path,
                'thumbnail_path' => $thumbnail_path,
                'pdf' => $pdf,
                'is_favorite' => $request->boolean('is_favorite'),
                'is_featured' => $request->boolean('is_featured'),
                'mentions' => json_encode($mentions),
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_upload_success',
                'description' => 'Uploaded article: ' . $Article->title,
            ]);


            return redirect()->route('content.articles')->with('success', 'Article uploaded successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Article upload error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_upload_error',
                'description' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Article upload failed. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $article = Article::with('category')->findOrFail($id);
            $categories = Category::all();
            $users = User::all();
            return view('pages.content.edit_article', compact('article', 'categories', 'users'));
        } catch (Exception $e) {
            LaravelLog::error('Article edit error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load article for editing.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'year' => 'required|digits:4',
                'month' => 'required',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
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


            $image_path = $article->image_path;
            if ($request->hasFile('image_path') && $request->file('image_path')->isValid()) {
                // Delete old PDF from Google Drive if exists

                if ($article->image_path) {
                    $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($article->image_path);
                    if ($fileId) {
                        $this->driveServiceThumbnail->deleteFile($fileId);
                    }
                }
                // Upload new PDF
                $filename = time() . '_' . $request->file('image_path')->getClientOriginalName();
                $image_path = $this->driveServiceThumbnail->uploadThumbnail($request->file('image_path'), $filename);
                $image_path = 'https://drive.google.com/file/d/' . $image_path . '/preview';
            }


            $thumbnail_path = $article->thumbnail_path;
            if ($request->hasFile('thumbnail_path') && $request->file('thumbnail_path')->isValid()) {
                // Delete old PDF from Google Drive if exists

                if ($article->thumbnail_path) {
                    $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($article->thumbnail_path);
                    if ($fileId) {
                        $this->driveServiceThumbnail->deleteFile($fileId);
                    }
                }
                // Upload new PDF
                $filename = time() . '_' . $request->file('thumbnail_path')->getClientOriginalName();
                $thumbnail_path = $this->driveServiceThumbnail->uploadThumbnail($request->file('thumbnail_path'), $filename);
                $thumbnail_path = 'https://drive.google.com/file/d/' . $thumbnail_path . '/preview';
            }



            // Update database
            $article->update([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'image_path' => $image_path,
                'image_path' => $thumbnail_path,
                'pdf' => $pdf,
                'is_featured' => $request->boolean('is_featured'),
                'is_favorite' => $request->boolean('is_favorite'),
                'mentions' => json_encode($mentions),
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_update_success',
                'description' => 'Updated article: ' . $article->title,
            ]);

            return redirect()->route('content.articles')->with('success', 'Article updated successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Article update error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_update_error',
                'description' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Article update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);

            // Delete PDF from Google Drive if exists
            if ($article->pdf) {
                $fileId = $this->driveServicePDF->getFileIdFromUrl($article->pdf);
                if ($fileId && !$this->driveServicePDF->deleteFile($fileId)) {
                    throw new Exception('Failed to delete PDF from Google Drive.');
                }
            }

            // Delete image from Google Drive if exists
            if ($article->image_path) {
                $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($article->image_path);
                if ($fileId && !$this->driveServiceThumbnail->deleteFile($fileId)) {
                    throw new Exception('Failed to delete image from Google Drive.');
                }
            }

            // Delete thumbnail from Google Drive if exists
            if ($article->thumbnail_path) {
                $fileId = $this->driveServiceThumbnail->getFileIdFromUrl($article->thumbnail_path);
                if ($fileId && !$this->driveServiceThumbnail->deleteFile($fileId)) {
                    throw new Exception('Failed to delete thumbnail from Google Drive.');
                }
            }

            // Delete article from database
            $article->delete();

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_delete_success',
                'description' => 'Deleted article: ' . $article->title,
            ]);

            return redirect()->route('content.articles')->with('success', 'Article deleted successfully.');
        } catch (Exception $e) {
            LaravelLog::error('Article delete error: ' . $e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_delete_error',
                'description' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete article: ' . $e->getMessage());
        }
    }
}
