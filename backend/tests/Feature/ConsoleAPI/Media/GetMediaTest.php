<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets media', function () {
    $this->blog = blogWithAccess();
    Media::factory()->count(3)->create([
        'blog_id' => $this->blog,
    ]);
    consoleApi($this->blog, 'GET', '/media')
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
    $this->blog = blogWithAccess();
    Media::factory()->count(3)->create([
        'blog_id' => $this->blog,
    ]);
    $media = Media::orderBy('id', 'ASC')
        ->where('blog_id', $this->blog->id)
        ->first();

    consoleApi($this->blog, 'GET', '/media', ['limit' => 1, 'offset' => 2])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has(1)
                ->first(fn (AssertableJson $json) => $json->where('id', $media->id)->etc())
        );
});

it('gets media with extensions', function() {

    $blog = blogWithAccess();

    $media = Media::factory()->count(3)->create([
        'blog_id' => $blog,
        'extension' => 'jpg',
    ]);

    $media->first()->update([
        'extension' => 'svg'
    ]);

    consoleApi($blog, 'GET', '/media', ['extensions' => ['svg']])
        ->assertOk()
        ->assertJsonCount(1);

});


it('gets media with image type', function() {

    $blog = blogWithAccess();

    $media = Media::factory()->count(3)->create([
        'blog_id' => $blog,
        'extension' => 'pdf',
    ]);

    $media[0]->update(['extension' => 'svg']);
    $media[1]->update(['extension' => 'jpg']);

    consoleApi($blog, 'GET', '/media', ['type' => 'image'])
        ->assertOk()
        ->assertJsonCount(2);

});

it('searches media', function() {

    $blog = blogWithAccess();
    $media = Media::factory()->count(5)->create([
        'blog_id' => $blog,
    ]);

    $media[0]->update(['name' => 'test']);
    $media[1]->update(['original_name' => 'testing']);
    $media[2]->update(['name' => 'old_test']);

    // just to make sure orWhere does not mess up the results
    $media[3]->update(['name' => 'test', 'blog_id' => $blog->id + 1]);

    consoleApi($blog, 'GET', '/media', ['search' => 'test'])
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJsonPath('0.name', 'old_test')
        ->assertJsonPath('1.original_name', 'testing')
        ->assertJsonPath('2.name', 'test');

});