<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_group_id','user_id','order', 'content', 'type'];

    protected $casts = [
        'type' => 'string', // Ensures enum is treated as string
    ];

    // A Question belongs to a QuestionGroup
    public function questionGroup()
    {
        return $this->belongsTo(QuestionGroup::class);
    }

    // A Question has many Answers
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
