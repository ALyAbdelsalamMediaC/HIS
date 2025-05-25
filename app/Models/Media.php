<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
  use HasFactory;

    protected $fillable = [
        'category_id', 'title', 'description','pdf',
        'file_path', 'thumbnail_path', 'is_featured', 'is_recommended'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'media_tags');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
