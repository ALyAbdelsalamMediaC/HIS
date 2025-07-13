<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'user_id',
        'title',
        'views',
        'description',
        'pdf',
        'status',
        'file_path',
        'duration',
        'thumbnail_path',
        'image_path',
        'is_featured',
        'assigned_to',
        'mention',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function AdminComment()
    {
        return $this->hasMany(AdminComment::class);
    }
    public function reviewas()
    {
        return $this->hasMany(Review::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'media_tags');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function bookMarks()
    {
        return $this->hasMany(Bookmark::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAssignedReviewersAttribute()
    {
        if (!$this->assigned_to) {
            return collect();
        }

        $reviewerIds = json_decode($this->assigned_to, true);
        if (!is_array($reviewerIds)) {
            return collect();
        }

        return User::whereIn('id', $reviewerIds)->get();
    }

    public function getImageAttribute($value)
    {
        return $value ? url('/storage/uploads/' . $value) : null;
    }

}
