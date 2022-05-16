<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates a post variant', function () {
    $language = $this->blog->languages[0];
    $language2 = $this->blog->languages[1];
    $post = $this->blog->posts()->first();

    $post->variants()->delete();

    $this->assertEquals(0, $post->variants()->count());

    $this
        ->callConsoleApi('POST', "/post/$post->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('status')->etc());


    $this
        ->callConsoleApi('POST', "/post/$post->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('status')->etc());

    $this->assertEquals(2, $post->variants()->count());
});
