<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'description'];

    public function media()
    {
        return $this->hasMany(Media::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
    public function policies()
    {
        return $this->hasMany(Policy::class, 'category_id');
    }
    public function subCategories()
{
    return $this->hasMany(SubCategory::class, 'category_id');
}
}
