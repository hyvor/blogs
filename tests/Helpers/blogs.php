<?php


use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;

function blog($attrs = []) : Blog {
    $blog = Blog::factory()
        // ->has(BlogVariant::factory(), 'variants')
        ->create($attrs);

    return $blog;
}

function blogWithAccess($attrs = []) {
    $blog = blog($attrs + ['hyvor_user_id' => 1]);

    User::factory()->create([
        'blog_id' => $blog->id,
        'hyvor_user_id' => 1,
        'role' => 'owner',
    ]);

    return $blog;
}

function blogWithAccessLanguageAndRoutes($attrs = []) {
    $blog = blogWithAccess($attrs);
    addPrimaryLanguage($blog);
    addBlogVariants($blog);
    addDefaultRoutes($blog);
    return $blog;
}

function blogWithLanguage($attrs = []) {
    $blog = blog($attrs);
    addPrimaryLanguage($blog);
    addBlogVariants($blog);
    return $blog;
}

function blogWithLanguageAndRoutes($attrs = []) {
    $blog = blog($attrs);
    addPrimaryLanguage($blog);
    addBlogVariants($blog);
    addDefaultRoutes($blog);
    return $blog;
}

function addBlogVariants(Blog $blog, $languages = null) {

    $languages = $languages ? collect(Arr::wrap($languages)) : $blog->languages;

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
function previewBlog() : Blog {
    return blog(['type' => BlogTypeEnum::PREVIEW]);
}