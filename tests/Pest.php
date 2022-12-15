<?php

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Media\MediaRepository;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\User;
use Faker\Factory;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

uses()->beforeEach(function () {
    $this->blog = Blog::find(config('test.blog_id'));
    $this->user = User::where('hyvor_user_id', config('test.hyvor_user_id'))->first();

    // reset userbase
    Userbase::$FAKE = null;

    // disable uploading profile picture
    $this->mock(MediaRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFromUrl')
            ->andReturn(Media::factory()->create());
    });

    Http::fake([
        'https://iframe.ly/api/iframely*' => Http::response(jsonData('UrlData/iframely-response.json'))
    ]);

})->in('Feature', 'Unit');

/*function blog()
{
    return Blog::find(config('test.blog_id'));
}*/

/*function blog(BlogTypeEnum $type = BlogTypeEnum::DEFAULT)
{
    return Blog::factory()->has(
        BlogVariant::factory(),
        'variants'
    )->create([
        'type' => $type,
    ]);
}*/

/*function post()
{
    return Post::factory()->create(['blog_id' => config('test.blog_id')]);
}

function postWithVariant($post = [], $variant = [])
{
    return Post::factory()
    ->has(PostVariant::factory()->state($variant), 'variants')
    ->create($post);
}

function aPublishedPost()
{
    $post = Post::where(['is_page' => false, 'blog_id' => config('test.blog_id')])->first();
    $post->variants->map(fn ($variant) => $variant->update(['status' => 'published']));

    return $post;
}

function seedPublishedPosts(int $count, Blog $blog = null, $isPage = false)
{
    $blog ??= blog();

    return Post::factory()->count($count)
        ->has(
            PostVariant::factory()->state([
                'status' => 'published',
                'language_id' => $blog->languages()->where('is_primary', true)->first()->id,
            ]),
            'variants'
        )->create([
            'blog_id' => $blog,
            'is_page' => $isPage,
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
}*/

function faker()
{
    return Factory::create();
}

function test_unit_data_path($path = '')
{
    return base_path('tests/Unit/__DATA__/'.$path);
}
function jsonData(string $filename)
{
    $filename = trim($filename, '/');

    return json_decode(file_get_contents(base_path('tests/Unit/__DATA__/'.$filename)), true);
}

// https://www.youtube.com/watch?v=l3kioTuYt98
function createRequest($method, $uri)
{
    $symfonyRequest = SymfonyRequest::create($uri, $method);

    return Request::createFromBase($symfonyRequest);
}

function testTwigRendering(string $template, array $vars, string $expectation)
{
    $vars = json_decode(json_encode($vars), true);
    $val = TwigRenderer::renderString($template, $vars);
    expect($val)->toBe($expectation);
}

foreach (glob('tests/helpers/*.php') as $file) {
    include_once $file;
}
