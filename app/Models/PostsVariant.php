<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostsVariant extends Model
{
    use HasFactory;

    protected $casts = [
        'published_at' => 'datetime',
    ];


    public function post()
    {
        return $this->belongTo(Post::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
