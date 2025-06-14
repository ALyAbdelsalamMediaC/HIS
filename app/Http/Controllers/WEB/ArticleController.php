<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use App\Services\GoogleDriveService; // Make sure this service is built
use Illuminate\Support\Facades\Storage;


class ArticleController extends Controller
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

            $article = Article::with('category')->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
            ;

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
        return view('pages.content.add_article', compact('categories'));
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'iamge_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'is_featured' => 'nullable|boolean',
            ]);



            // Store thumbnail if exists
            $iamge_path = null;
            if ($request->hasFile('iamge_path')) {
                $iamge_path = $request->file('iamge_path')->store('iamge_path', 'public');
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
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'iamge_path' => $iamge_path,
                'pdf'=> $pdf,
                'is_featured' => $request->boolean('is_featured'),
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


            return view('pages.content.edit_article', compact('article', 'categories'));
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
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'iamge_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'pdf' => 'nullable|file|mimes:pdf|max:51200', // 50MB limit
                'is_featured' => 'nullable|boolean',
            ]);

            // Update PDF file on Google Drive
            $pdf = $article->pdf;
            if ($request->hasFile('pdf') && $request->file('pdf')->isValid()) {
                // Delete old PDF from Google Drive if exists
                if ($article->pdf) {
                    $fileId = $this->driveService->getFileIdFromUrl($article->pdf);
                    if ($fileId) {
                        $this->driveService->deleteFile($fileId);
                    }
                }
                // Upload new PDF
                $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();
                $pdf = $this->driveService->uploadFile($request->file('pdf'), $filename);
            }

            // Update image if exists
            $iamge_path = $article->iamge_path;
            if ($request->hasFile('iamge_path') && $request->file('iamge_path')->isValid()) {
                // Delete old image from storage if exists
                if ($article->iamge_path) {
                    Storage::disk('public')->delete($article->iamge_path);
                }
                $iamge_path = $request->file('iamge_path')->store('iamge_path', 'public');
            }

            // Update database
            $article->update([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'iamge_path' => $iamge_path,
                'pdf' => $pdf,
                'is_featured' => $request->boolean('is_featured'),
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
}
