<?php


use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use App\Models\BlogVariant;

function blog($attrs = []) : Blog {
    $blog = Blog::factory()
        ->has(BlogVariant::factory(), 'variants')
        ->create($attrs);

    return $blog;
}

function devBlog() : Blog {
    return blog(['type' => BlogTypeEnum::DEV]);
}