<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $mediaCount = Media::count();
        $usersCount = User::count();
        $articlesCount = Article::count();
        $commentsCount = Comment::count();

        return view('pages.dashboard.index'); // Return the dashboard view
    }
}
