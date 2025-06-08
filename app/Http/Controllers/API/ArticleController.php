<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show()
    {
        try {
            $categories = Article::all();

            return response()->json($categories, 200);
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
