<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'added_by',
        'category_id',
        'body',
        'added_by',
    ];

    /**
     * Get the user that added the policy.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    public function category()
    {
        return $this->belongsTo(PolicyCategory::class, 'category_id');
    }
}
