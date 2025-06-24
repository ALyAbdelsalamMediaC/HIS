<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyCategory extends Model
{
    use HasFactory;
    protected $table = 'policy_categories';
    protected $fillable = [
        'title',
    ];
    public function policies()
    {
        return $this->hasMany(Policy::class, 'category_id');
    }
}