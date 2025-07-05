<?php

namespace App\Http\Controllers\API;

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
                return response()->json([
                    'type' => 'article',
                    'data' => $article
                ], 200);
            }

            $media = Media::where('title', 'like', '%' . $searchTerm . '%')->first();
            if ($media) {
                return response()->json([
                    'type' => 'media',
                    'data' => $media
                ], 200);
            }

            // If not found, return not found response
            return response()->json([
                'message' => 'No results found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during search.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
