<?php


use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;

function blog($attrs = []) : Blog {
    $blog = Blog::factory()->create($attrs);

    return $blog;
}

function devBlog() : Blog {
    return blog(['type' => BlogTypeEnum::DEV]);
}