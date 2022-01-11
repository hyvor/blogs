<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'name',
        'slug',
        'description',
        'feature_image_media_id',
    ];
}
