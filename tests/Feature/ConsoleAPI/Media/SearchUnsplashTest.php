<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Services\UnsplashService;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;

it('searches unsplash', function () {
    $json = jsonData('Media/unsplash-search.json');

    $this->mock(UnsplashService::class, function (MockInterface $mock) use ($json) {
        $mock
            ->shouldReceive('search')
            ->once()
            ->andReturn(collect($json['results']));
    });

    $this->callConsoleApi('GET', '/media/unsplash/search', [
        'search' => 'test',
        'page' => 1,
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count(1)
                ->each(function (AssertableJson $json) {
                    $json->where('url', 'https://images.unsplash.com/photo-1416339306562-f3d12fefd36f?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1080&fit=max&s=92f3e02f63678acc8416d044e189f515')
                        ->where('title', 'A man drinking a coffee.')
                        ->where('author', 'Jeff Sheldon')
                        ->where('author_url', 'http://unsplash.com/@ugmonk')
                        ->etc();
                });
        });
});
