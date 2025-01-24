<?php

use App\Domains\Delivery\Twig\TwigRenderer;
use App\Models\Blog;
use App\Models\User;
use Faker\Factory;
use Hyvor\Internal\Auth\Providers\Fake\FakeProvider;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

uses()->beforeEach(function () {

    $this->blog = Blog::find(config('test.blog_id'));
    $this->user = User::where('hyvor_user_id', config('test.hyvor_user_id'))->first();

    // reset userbase
    FakeProvider::databaseClear();
    Billing::fake(new BlogsLicense());

    Cache::flush();

    // disable uploading profile picture
//    $this->mock(MediaRepository::class, function (MockInterface $mock) {
//        $mock->shouldReceive('uploadFromUrl')
//            ->andReturn(Media::factory()->create(['blog_id' => 0]));
//    });

//    Http::fake([
//        'https://iframe.ly/api/iframely*' => Http::response(jsonData('UrlData/iframely-response.json'))
//    ]);


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
