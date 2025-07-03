<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Media comment like model
class LikeCommentArticle extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'comment_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentArticle()
    {
        return $this->belongsTo(CommentArticle::class);
    }
}

