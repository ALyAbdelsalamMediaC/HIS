<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionGroup extends Model
{
    protected $fillable = ['user_id','name', 'description'];

    // A QuestionGroup has many Questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
