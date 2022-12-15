<?php


use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;

function blog($attrs = []) : Blog {
    $blog = Blog::factory()
        // ->has(BlogVariant::factory(), 'variants')
        ->create($attrs);

    return $blog;
}

function addBlogVariants(Blog $blog, array|Language $languages) {

    $languages = collect(Arr::wrap($languages));

    return BlogVariant::factory()
        ->count($languages->count())
        ->state(new Sequence(
            ...$languages->map(fn($lang) => ['language_id' => $lang->id])
        ))
        ->create([
            'blog_id' => $blog
        ]);

}

function devBlog() : Blog {
    return blog(['type' => BlogTypeEnum::DEV]);
}