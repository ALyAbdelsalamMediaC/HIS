<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'image_path', 'video_path', 'description'];

    public function media()
    {
        return $this->hasMany(Media::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
