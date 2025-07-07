<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckUpdate extends Model
{
  protected $fillable = [
        'ios_version',
        'android_version',
        'ios',
        'android',
        'android_link',
        'ios_link',
    ];
    protected $hidden = ['id'];

    // Ensure only one row exists
    public static function getInstance()
    {
        return static::firstOrCreate();
    }
}
