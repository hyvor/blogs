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

    /**
     * Eager load with these relations
     * because these are always wanted
     */
    protected $with = [
        'tags',
        'authors',
        'language'
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withPivot('order')->orderBy('order', 'ASC');
    }

    public function authors()
    {
        return $this->belongsToMany(User::class, 'post_author')->withPivot('order')->orderBy('order', 'ASC');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }


}
