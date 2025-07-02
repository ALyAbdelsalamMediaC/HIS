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

            if ($user->role === 'reviewer') {
                // For reviewers: get media where they are in assigned_to or status is pending
                $mediaCountPublished = Media::where('status', 'published')
                    ->whereJsonContains('assigned_to', $user->id)
                    ->count();

                $mediaCountPending = Media::where('status', 'inreview')
                    ->count();

                $lastPublishedMedia = Media::whereIn('status', ['published', 'inreview'])
                    ->whereJsonContains('assigned_to', $user->id)
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
            $commentsVideo = Comment::count();
            $commentsArticleCount = CommentArticle::count();
            $commentsCount = $commentsVideo + $commentsArticleCount;

            return view('pages.admin.dashboard', compact('mediaCountPublished', 'mediaCountPending','mediaCountInreview', 'usersCount', 'commentsCount', 'lastPublishedMedia', 'lastPublishedMediaCommentsCount'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to retrieve dashboard data.']);
        }
    }
}
