<?php

namespace App\Models;

use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;
    use Metable;

    // meta
    protected function metaDefinition(Definer $definer)
    {
        $definer->add('authors_count')->type('int|null')->default(null);
        $definer->add('tags_count')->type('int|null')->default(null);
        $definer->add('posts_count')->type('int|null')->default(null);
        $definer->add('pages_count')->type('int|null')->default(null);
    }
}
