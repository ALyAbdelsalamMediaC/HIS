<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateArticle extends Model
{
protected $table = 'rate_article';

    protected $fillable = [
        'article_id',
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


    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
