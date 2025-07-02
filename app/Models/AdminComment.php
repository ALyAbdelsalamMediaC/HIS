<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminComment extends Model
{
   use HasFactory;

    protected $fillable = ['user_id', 'media_id', 'parent_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function parent()
    {
        return $this->belongsTo(AdminComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(AdminComment::class, 'parent_id');
    }
}
