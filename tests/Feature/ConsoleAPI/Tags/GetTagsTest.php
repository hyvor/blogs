<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Models\Tag;
use Illuminate\Testing\Fluent\AssertableJson;

it('fetches tags', function () {
    $tagsCount = Tag::where('blog_id', config('test.blog_id'))->count();
    $currentCount = floor($tagsCount / 2);
    $this
        ->callConsoleApi('GET', '/tags', [
            'limit' => $currentCount,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($currentCount) {
            $json->count($currentCount)
                ->has('0', function (AssertableJson $json) {
                    $json->has('id')
                        ->has('slug')
                        ->etc();
                });
        });
});

it('fetches tags with offset', function () {
    $tagsCount = Tag::where('blog_id', config('test.blog_id'))->count();
    $this
        ->callConsoleApi('GET', 'tags', [
            'limit' => $tagsCount,
            'offset' => $tagsCount - 1,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count(1);
        });
});
