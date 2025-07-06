<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use App\Models\User;
use App\Models\CommentArticle;
use App\Models\Like;
use App\Models\LikeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        try {
            $user = Auth::user();

            // Initialize all variables to avoid undefined variable errors
            $mediaCountPublished = 0;
            $mediaCountPending = 0;
            $mediaCountInreview = 0;
            $lastPublishedMedia = null;
            $topVideos = collect();
            $topArticles = collect();

            try {
                if ($user->role === 'reviewer') {
                    // For reviewers: get media where they are in assigned_to or status is pending
                    $mediaCountPublished = Media::where('status', 'published')
                        ->where(function($query) use ($user) {
                            $query->whereJsonContains('assigned_to', $user->id);
                        })
                        ->count();

                    // For reviewers, pending = 'inreview' assigned to them, inreview = all 'inreview'
                    $mediaCountPending = Media::where('status', 'inreview')
                        ->where(function($query) use ($user) {
                            $query->whereJsonContains('assigned_to', $user->id);
                        })
                        ->count();
                    $mediaCountInreview = Media::where('status', 'inreview')->count();

                    $lastPublishedMedia = Media::whereIn('status', ['published', 'inreview'])
                        ->where(function($query) use ($user) {
                            $query->whereJsonContains('assigned_to', $user->id);
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();

                    // Top 5 published videos for reviewers (only those assigned to them)
                    $topVideos = Media::with(['user', 'comments'])
                        ->where('status', 'published')
                        ->where(function($query) use ($user) {
                            $query->whereJsonContains('assigned_to', $user->id);
                        })
                        ->withCount('likes')
                        ->orderBy('likes_count', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

                } else {
                    // For other roles (admin, user)
                    $mediaCountPublished = Media::where('status', 'published')->count();
                    $mediaCountPending = Media::where('status', 'pending')->count();
                    $mediaCountInreview = Media::where('status', 'inreview')->count();

                    $lastPublishedMedia = Media::where('status', 'published')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    // Top 5 published videos for all users
                    $topVideos = Media::with(['user', 'comments'])
                        ->where('status', 'published')
                        ->withCount('likes')
                        ->orderBy('likes_count', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                }
            } catch (\Exception $e) {
                \Log::error('Media queries error: ' . $e->getMessage());
                // Continue with empty collections
            }

            $lastPublishedMediaCommentsCount = 0;
            if ($lastPublishedMedia) {
                try {
                    $lastPublishedMediaCommentsCount = Comment::where('media_id', $lastPublishedMedia->id)->count();
                } catch (\Exception $e) {
                    \Log::error('Comments count error: ' . $e->getMessage());
                }
            }

            try {
                $usersCount = User::count();
                $articlesCount = Article::count();
            } catch (\Exception $e) {
                \Log::error('Count queries error: ' . $e->getMessage());
                $usersCount = 0;
                $articlesCount = 0;
            }

            // Top 5 articles based on likes
            try {
                $topArticles = Article::with(['user', 'commentarticle'])
                    ->withCount('likesarticle')
                    ->orderBy('likesarticle_count', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                \Log::error('Articles query error: ' . $e->getMessage());
                $topArticles = collect();
            }

            return view('pages.admin.dashboard', compact(
                'mediaCountPublished', 
                'mediaCountPending',
                'mediaCountInreview', 
                'usersCount', 
                'articlesCount', 
                'lastPublishedMedia', 
                'lastPublishedMediaCommentsCount',
                'topVideos',
                'topArticles'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Failed to retrieve dashboard data.']);
        }
    }
}
