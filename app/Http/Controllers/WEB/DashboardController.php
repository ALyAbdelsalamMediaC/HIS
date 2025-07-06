<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use App\Models\User;
use App\Models\CommentArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            } else {
                // For other roles (admin, user)
                $mediaCountPublished = Media::where('status', 'published')->count();
                $mediaCountPending = Media::where('status', 'pending')->count();
                $mediaCountInreview = Media::where('status', 'inreview')->count();

                $lastPublishedMedia = Media::where('status', 'published')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $lastPublishedMediaCommentsCount = 0;
            if ($lastPublishedMedia) {
                $lastPublishedMediaCommentsCount = Comment::where('media_id', $lastPublishedMedia->id)->count();
            }

            $usersCount = User::count();
            $articlesCount = Article::count();

            return view('pages.admin.dashboard', compact('mediaCountPublished', 'mediaCountPending','mediaCountInreview', 'usersCount', 'articlesCount', 'lastPublishedMedia', 'lastPublishedMediaCommentsCount'));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Failed to retrieve dashboard data.']);
        }
    }
}
