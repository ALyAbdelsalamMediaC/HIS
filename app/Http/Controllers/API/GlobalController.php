<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Media;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    public function globalSearch(Request $request)
    {
        try {
            $searchTerm = $request->input('search');
            $userId = (int) $request->user_id;

            $Media = Media::where('title', 'like', '%' . $searchTerm . '%')
            ->where('status', 'published')
            ->with(['category'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->first();
            if (is_null($Media)) {
            throw new \Exception('No media found for the given search criteria.');
        }
            $Media->is_favorite = Bookmark::where('user_id', $userId)->where('media_id',$Media->id)->exists();
            if ($Media) {
                return response()->json([
                    'type' => 'media',
                    'data' => [$Media]
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
