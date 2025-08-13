<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
   protected $fillable = ['question_id','user_id', 'content', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // An Answer belongs to a Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
