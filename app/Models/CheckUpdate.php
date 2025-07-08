<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckUpdate extends Model
{
    protected $table = 'check_updates';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = true;

    public static function getInstance()
    {
        return static::first() ?? static::create([
            'ios_version' => 1,
            'android_version' => 1,
            'ios' => false,
            'android' => false,
            'android_link' => '',
            'ios_link' => ''
        ]);
    }

    public function updateInstance(array $attributes)
    {
        return static::query()->update($attributes);
    }
}