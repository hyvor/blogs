<?php

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\PostSearchRepository;
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
use Illuminate\Support\Facades\DB;
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
            ->andReturn(Media::factory()->create(['blog_id' => 0]));
    });


    Http::fake([
        'https://iframe.ly/api/iframely*' => Http::response(jsonData('UrlData/iframely-response.json'))
    ]);

    $this->artisan('scout:flush "App\\\\Models\\\\PostVariant"');
    $this->artisan('scout:sync-index-settings');

})->in('Feature', 'Unit');

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
    $val = trim(TwigRenderer::renderString($template, $vars));
    expect($val)->toBe($expectation);
}

foreach (glob('tests/helpers/*.php') as $file) {
    include_once $file;
}
