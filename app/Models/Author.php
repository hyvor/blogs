<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    protected $table = "theme_files";

    protected $fillable = [
       'blog_id',
       'user_id',
       'status',
       'role',
    ];
}
