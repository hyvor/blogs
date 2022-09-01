<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Models\Media;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    Media::truncate(); // media factory-created models are added when mocking
    Media::factory()->count(3)->create([
        'blog_id' => blog(),
    ]);
});

it('gets media', function () {
    $this->callConsoleApi('GET', '/media')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has(3)
                ->each(function (AssertableJson $json) {
                    $json->has('id')
                        ->has('original_name')
                        ->has('url')
                        ->etc();
                });
        });
});

it('limit and offset works and orders by ID desc', function () {
    $media = Media::orderBy('id', 'ASC')->first();

    $this->callConsoleApi('GET', '/media', ['limit' => 1, 'offset' => 2])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has(1)
                ->first(fn (AssertableJson $json) => $json->where('id', $media->id)->etc())
        );
});
