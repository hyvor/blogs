<?php

namespace Tests\Unit\Domains\Cache;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Cache\CacheService;
use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

it('clears template cache by setting timestamp and emits the event', function() {

    Event::fake();

    $blog = blog();

    $templateClearedKey = CacheService::LAST_TEMPLATE_CACHE_CLEARED_AT;
    $key = "blog_cache_{$blog->id}_$templateClearedKey";
    expect(Cache::get($key))->toBeNull();

    $cache = new CacheService();
    $cache->blog($blog)->clearTemplateCache();

    expect(Cache::get($key))->toBeInt();

    Event::assertDispatched(CacheClearTemplatesEvent::class);

});

it('clears all cache by settings timestamp and emits the event', function() {

    Event::fake();

    $blog = blog();

    $clearKey = CacheService::LAST_ALL_CACHE_CLEARED_AT;
    $key = "blog_cache_{$blog->id}_$clearKey";
    expect(Cache::get($key))->toBeNull();

    $cache = new CacheService();
    $cache->blog($blog)->clearAllCache();

    expect(Cache::get($key))->toBeInt();

    Event::assertDispatched(CacheClearAllEvent::class);

});

it('sets cache', function() {

    $blog = blog();
    $responseObject = DeliveryAPIResponseObject::forFile(
        DeliveryAPIFileTypeEnum::TEMPLATE,
        'test'
    );

    $cache = new CacheService();
    $cache->blog($blog)->set('/test', $responseObject);

    expect(Cache::get("blog_cache_{$blog->id}_/test"))->not->toBeNull();

});

it('gets from cache', function() {

    $blog = blog();
    $responseObject = DeliveryAPIResponseObject::forFile(
        DeliveryAPIFileTypeEnum::TEMPLATE,
        'test'
    );

    $cache = new CacheService();
    $cache->blog($blog)->set('/test', $responseObject);

    expect($cache->get('/test'))->not->toBeNull();

});

it('returns null when template cache is cleared', function() {

    $blog = blog();
    $responseObject = DeliveryAPIResponseObject::forFile(
        DeliveryAPIFileTypeEnum::TEMPLATE,
        'test'
    );

    $cache = new CacheService();
    $cache->blog($blog)->set('/test', $responseObject);

    Cache::put("blog_cache_{$blog->id}_LAST_TEMPLATE_CACHE_CLEARED_AT", now()->addDay()->timestamp);

    expect($cache->get('/test'))->toBeNull();

});

it('returns null when whole cache is cleared', function() {

    $blog = blog();
    $responseObject = DeliveryAPIResponseObject::forFile(
        DeliveryAPIFileTypeEnum::TEMPLATE,
        'test'
    );

    $cache = new CacheService();
    $cache->blog($blog)->set('/test', $responseObject);

    Cache::put("blog_cache_{$blog->id}_LAST_ALL_CACHE_CLEARED_AT", now()->addDay()->timestamp);

    expect($cache->get('/test'))->toBeNull();

});

it('returns null when cache is not there', function() {
    $blog = blog();
    $cache = new CacheService();
    $cache->blog($blog);
    expect($cache->get('/test'))->toBeNull();
});

it('returns null when delivery API object is not set correctly', function() {
    $blog = blog();
    Cache::put("blog_cache_{$blog->id}_/test", serialize('nothing'));

    $cache = new CacheService();
    $cache->blog($blog);
    expect($cache->get('/test'))->toBeNull();
});