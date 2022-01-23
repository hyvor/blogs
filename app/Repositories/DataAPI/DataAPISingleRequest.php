<?php

namespace App\Domains\DataAPI;

use App\Models\Blog;

class DataAPISingleRequest
{
    private $blog;
    private $id;
    private $slug;
    private $keys;

    public function getBlog()
    {
        return $this->blog;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getSlug()
    {
        return $this->slug;
    }
    public function getKeys()
    {
        return $this->keys;
    }

    public function setBlogId(Blog $blog)
    {
        $this->blog = $blog;
        return $this;
    }
    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;
        return $this;
    }
    public function setKeys(?string $keys)
    {
        $this->keys = $keys;
        return $this;
    }
}
