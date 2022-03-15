<?php

namespace App\Models;

use App\Domains\Post\PostLanguageRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Eager load with these relations
     * because these are always wanted
     */
    protected $with = [
        'variants',
        'tags',
        'authors',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }


    public function variants()
    {
        return $this->hasMany(PostsVariant::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withPivot('order')->orderBy('order', 'ASC');
    }

    public function authors()
    {
        return $this->belongsToMany(User::class, 'post_author')->withPivot('order')->orderBy('order', 'ASC');
    }

}
