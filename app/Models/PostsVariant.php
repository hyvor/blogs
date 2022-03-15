<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostsVariant extends Model
{
    use HasFactory;


    public function post()
    {
        $this->belongTo(Post::class);
    }

    public function language()
    {
        $this->belongsTo(Language::class);
    }
}
