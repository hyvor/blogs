<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogThemeFile extends Model
{
    use HasFactory;

    protected $table = "blog_theme_files";

    protected $fillable = [
        'blog_id',
        'name',
        'content',
        'type',
    ];
}
