<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Media;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    public function globalSearch(Request $request)
    {
        try {
            $searchTerm = $request->input('search');

            // Search in Article and Media models by title
            $article = Article::where('title', 'like', '%' . $searchTerm . '%')->first();
            if ($article) {
                return redirect()->route('content.article', ['id' => $article->id]);
            }

            $media = Media::where('title', 'like', '%' . $searchTerm . '%')->first();
            if ($media) {
                // Assuming 'published' as default status, adjust as needed
                return redirect()->route('content.video', ['id' => $media->id, 'status' => $media->status ?? 'published']);
            }

            // If not found, return to search with a message
            return redirect()->back()->with('error', 'No results found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred during search.');
        }
    }
}
