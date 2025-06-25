<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
     use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'route',
        'sender_id',
        'receiver_id',
        'media_id',
        'article_id',
        'seen'
    ];

     public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

}
