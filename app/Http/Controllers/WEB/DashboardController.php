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
        $mediaCountPublished = Media::where('status', operator: 'published')->count();
        $mediaCountPending = Media::where('status', operator: 'pending')->count();

        $usersCount = User::count();

        $commentsVideo = Comment::count();
        $commentsArticleCount = CommentArticle::count();
        $commentsCount = $commentsVideo + $commentsArticleCount;

        return view('pages.admin.dashboard', compact('mediaCountPublished', 'mediaCountPending', 'usersCount', 'commentsCount'));
    }
}
