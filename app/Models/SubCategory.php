<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'sub_category';

    protected $fillable = [
        'category_id',
        'name',
        'description',
    ];

    // Relationship: SubCategory belongs to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
