<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostsVariant extends Model
{
    use HasFactory;



    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
