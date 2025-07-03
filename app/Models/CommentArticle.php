<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Article comment model
class CommentArticle extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'aticle_id', 'parent_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function parent()
    {
        return $this->belongsTo(CommentArticle::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(CommentArticle::class, 'parent_id');
    }

    public function likeCommentArticles()
    {
        return $this->hasMany(LikeCommentArticle::class);
    }
}
