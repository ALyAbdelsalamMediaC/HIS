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

            $articles = \App\Models\Article::where('title', 'like', '%' . $searchTerm . '%')->get();
            $videos = \App\Models\Media::where('title', 'like', '%' . $searchTerm . '%')->get();

            if ($request->has('ajax')) {
                $articlesArr = $articles->map(function($a) {
                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'thumbnail' => $a->thumbnail ?? $a->thumbnail_path ?? null,
                        'created_at' => optional($a->created_at)->toDateString(),
                    ];
                });
                $videosArr = $videos->map(function($v) {
                    return [
                        'id' => $v->id,
                        'title' => $v->title,
                        'thumbnail' => $v->thumbnail ?? $v->thumbnail_path ?? null,
                        'created_at' => optional($v->created_at)->toDateString(),
                        'status' => $v->status ?? 'published',
                    ];
                });
                return response()->json([
                    'articles' => $articlesArr,
                    'videos' => $videosArr,
                ]);
            }

            return view('pages.search.results', [
                'articles' => $articles,
                'videos' => $videos,
                'searchTerm' => $searchTerm,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred during search.');
        }
    }
}
