<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use App\Models\User;
use App\Models\CommentArticle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
                try {
            $mediaCountPublished = Media::where('status', 'published')->count();
            $mediaCountPending = Media::where('status', 'pending')->count();

            // $topMedia = Media::withCount('likes')
            // ->orderBy('likes_count', 'desc')
            // ->take(5)
            // ->get();
            $lastPublishedMedia = Media::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->first();

            $lastPublishedMediaCommentsCount = 0;
            if ($lastPublishedMedia) {
                $lastPublishedMediaCommentsCount = \App\Models\Comment::where('media_id', $lastPublishedMedia->id)->count();
            }

            // $topArticle = Article::withCount('LikeArticle')
            //     ->orderBy('likes_count', 'desc')
            //     ->take(5)
            //     ->get();

                
            $usersCount = User::count();

            $commentsVideo = Comment::count();
            $commentsArticleCount = CommentArticle::count();
            $commentsCount = $commentsVideo + $commentsArticleCount;

            return view('pages.admin.dashboard', compact('mediaCountPublished', 'mediaCountPending', 'usersCount', 'commentsCount', 'lastPublishedMedia', 'lastPublishedMediaCommentsCount'));
        } catch (\Exception $e) {
            // You can log the error or handle it as needed
            return back()->withErrors(['error' => 'Failed to retrieve dashboard data.']);
        }
    }
    // public function topMedia()
    // {
    //     try {
    //         $topMedia = Media::withCount('likes')
    //             ->orderBy('likes_count', 'desc')
    //             ->take(5)
    //             ->get();

    //         return view('pages.admin.dashboard', compact('topMedia'));
    //     } catch (\Exception $e) {
    //         // You can log the error or handle it as needed
    //         return back()->withErrors(['error' => 'Failed to retrieve top media.']);
    //     }
    // }

    // public function topArticle()
    // {
    //     try {
    //         $topArticle = Article::withCount('likesArticle')
    //             ->orderBy('likes_count', 'desc')
    //             ->take(5)
    //             ->get();

    //         return view('pages.admin.dashboard', compact('topArticle'));
    //     } catch (\Exception $e) {
    //         // You can log the error or handle it as needed
    //         return back()->withErrors(['error' => 'Failed to retrieve top article.']);
    //     }
    // }
    // public function lastPublished(){
    //     try {
    //         $lastPublishedMedia = Media::where('status', 'published')
    //             ->orderBy('created_at', 'desc')
    //             ->take(1)
    //             ->get();

    //         return view('pages.admin.dashboard', compact('lastPublishedMedia'));
    //     } catch (\Exception $e) {
    //         // You can log the error or handle it as needed
    //         return back()->withErrors(['error' => 'Failed to retrieve last published media.']);
    //     }
    // }
    
}
