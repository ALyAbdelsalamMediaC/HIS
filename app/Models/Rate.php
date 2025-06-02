<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
 protected $table = 'rate';

    protected $fillable = [
        'media_id',
        'user_id',
        'rate',
    ];

    protected $casts = [
        'rate' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    // public function article()
    // {
    //     return $this->belongsTo(Article::class)->where('type', 'article');
    // }

    // Optional: Helper method to get the rateable entity (media or article)
    // public function rateable()
    // {
    //     if ($this->type === 'media' && $this->media_id) {
    //         return $this->media;
    //     }

    //     if ($this->type === 'article' && $this->article_id) {
    //         return $this->article;
    //     }

    //     return null;
    // }
}
