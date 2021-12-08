<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogThemeFiles extends Model
{
    use HasFactory;

    protected $table = 'blog_theme_files';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'blog_id','theme_files','name','content'
    ];
}
