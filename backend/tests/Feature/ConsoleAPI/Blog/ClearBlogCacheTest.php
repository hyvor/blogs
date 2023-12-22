<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Cache\CacheService;

it('clears all cache', function() {

    $this->mock(CacheService::class, function ($mock) {
        $mock->shouldReceive('blog')->andReturnSelf();
        $mock->shouldReceive('clearAllCache')->once();
    });

    $blog = blogWithAccess();
    consoleApi($blog, 'DELETE', '/blog/cache', [
        'type' => 'all'
    ])
        ->assertOk();

});

it('cleas template cache', function() {

    $this->mock(CacheService::class, function ($mock) {
        $mock->shouldReceive('blog')->andReturnSelf();
        $mock->shouldReceive('clearTemplateCache')->once();
    });

    $blog = blogWithAccess();
    consoleApi($blog, 'DELETE', '/blog/cache', [
        'type' => 'template'
    ])
        ->assertOk();

});

it('clears path caches', function() {

    $this->mock(CacheService::class, function ($mock) {
        $mock->shouldReceive('blog')->andReturnSelf();
        $mock->shouldReceive('clearPathsCache')->once()->with(['path1', 'path2']);
    });

    $blog = blogWithAccess();
    consoleApi($blog, 'DELETE', '/blog/cache', [
        'type' => 'paths',
        'paths' => ['path1', 'path2']
    ])
        ->assertOk();

});