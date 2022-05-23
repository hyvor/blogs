<?php

namespace App\Data\Objects\ConsoleAPI\Counts;

use App\Models\Tag;

class TagCountObject
{

    public int $id;
    public string $name;
    public int $posts_count;

    // called from BlogCountsRepository with custom column names
    public function __construct(Tag $tag)
    {
        $this->id = $tag->id;
        $this->name = $tag->name;
        $this->posts_count = $tag->posts_count;
    }

}