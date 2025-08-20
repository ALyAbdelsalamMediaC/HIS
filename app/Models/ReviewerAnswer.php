<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerAnswer extends Model
{
    protected $fillable = ['question_id', 'user_id', 'media_id', 'content'];



    // An Answer belongs to a Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
