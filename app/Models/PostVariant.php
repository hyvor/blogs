<?php

namespace App\Models;

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PostVariant extends Model
{
    use HasFactory;
    use Searchable;
    
    protected $with = [
        'language'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function searchableAs()
    {
        return PostSearchRepository::SEARCH_INDEX_NAME;
    }

    public function toSearchableArray()
    {
        return PostSearchRepository::getSearchDocument($this);
    }

}
