<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'user_id',
        'title',
        'description',
        'image_path',
        'thumbnail_path',
        'hyperlink',
        'pdf',
        'is_featured',
        'is_favorite',
        'mention',
        'assigned_to',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function CommentArticle()
    {
        return $this->hasMany(CommentArticle::class);
    }
    public function likesArticle()
    {
        return $this->hasMany(LikeArticle::class);
    }
    
}
