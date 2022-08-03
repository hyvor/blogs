<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * Eager load with these relations
     * because these are always wanted
     */
    protected $with = [
        'variants',
        'tags',
        'authors',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_page' => 'boolean',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function variants()
    {
        return $this->hasMany(PostVariant::class);
    }

    public function tags()
    {
        return $this
            ->belongsToMany(Tag::class)
            ->withPivot('post_tag.id')
            ->orderBy('post_tag.id', 'ASC');
    }

    public function authors()
    {
        return $this
            ->belongsToMany(User::class, 'post_author')
            ->withPivot('post_author.id')
            ->orderBy('post_author.id', 'ASC');
    }
}
