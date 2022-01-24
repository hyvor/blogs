<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function authors() {
        return $this->belongsToMany(User::class, 'post_author');
    }
}
