<?php

use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\User;
use Faker\Factory;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

uses()->beforeEach(function () {
    $this->blog = Blog::find(config('test.blog_id'));
    $this->user = User::where('hyvor_user_id', config('test.hyvor_user_id'))->first();

    // reset userbase
    Userbase::$FAKE = null;
})->in('Feature', 'Unit');


function blog()
{
    return Blog::find(config('test.blog_id'));
}

function newBlog(BlogTypeEnum $type = BlogTypeEnum::DEFAULT)
{
    return Blog::factory()->has(
        BlogVariant::factory(),
        'variants'
    )->create([
        'type' => $type,
    ]);
}

function post()
{
    return Post::factory()->create(['blog_id' => config('test.blog_id')]);
}

function aPublishedPost()
{
    $post = Post::where(['is_page' => false, 'blog_id' => config('test.blog_id')])->first();
    $post->variants->map(fn ($variant) => $variant->update(['status' => 'published']));

    return $post;
}

function seedPublishedPosts(int $count, Blog $blog = null)
{
    $blog ??= blog();

    return Post::factory()->count($count)
        ->has(
            PostVariant::factory()->state([
                'status' => 'published',
                'language_id' => $blog->languages[0],
            ]),
            'variants'
        )->create([
            'blog_id' => $blog,
        ]);
}

function clearPosts(Blog $blog = null)
{
    $blog ??= blog();

    Post::where('blog_id', $blog->id)->delete();
}


function hyvorUser($fill = [])
{
    return HyvorUser::dummy($fill);
}

function faker()
{
    return Factory::create();
}


function test_unit_data_path($path = '')
{
    return base_path('tests/Unit/__DATA__/' . $path);
}
function jsonData(string $filename) {
    $filename = trim($filename, '/');
    return json_decode(file_get_contents(base_path('tests/Unit/__DATA__/' . $filename)), true);
}

// https://www.youtube.com/watch?v=l3kioTuYt98
function createRequest($method, $uri) {
    $symfonyRequest = SymfonyRequest::create($uri, $method);
    return Request::createFromBase($symfonyRequest);
}