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
class ArticleController extends Controller
{
    public function getall(Request $request)
    {
        try {
            $query = Article::with('category');

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
            $article = $query->orderBy('created_at', 'desc')->get();

            // Get all users with role 'reviewer'
            $reviewers = User::whereHas('roles', function ($q) {
                $q->where('name', 'reviewer');
            })->get();

            return view('pages.content.articles', compact('article', 'reviewers'));
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
        return view('pages.article.add', compact('categories'));
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'hyperlink' => 'nullable|url|max:2048',
                'description' => 'nullable|string',
                'iamge_path' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB limit
                'is_featured' => 'nullable|boolean',
                'is_recommended' => 'nullable|boolean',
            ]);



            // Store thumbnail if exists
            $iamge_path = null;
            if ($request->hasFile('iamge_path')) {
                $iamge_path = $request->file('iamge_path')->store('iamge_path', 'public');
            }

            // Save to database
            $Article = Article::create([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'name' => $validated['name'],
                'hyperlink' => $validated['hyperlink'],
                'description' => $validated['description'] ?? null,
                'status' => 'approved',
                'iamge_path' => $iamge_path,
                'is_featured' => $request->boolean('is_featured'),
                'is_recommended' => $request->boolean('is_recommended'),
            ]);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'article_upload_success',
                'description' => 'Uploaded article: ' . $Article->title,
            ]);

            return response()->json([
                'message' => 'Article uploaded successfully.',
                'article' => $Article
            ], 201);
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
