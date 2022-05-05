<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\PostVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a post and the primary variant', function () {
    $post = $this
        ->callConsoleApi('POST', '/post')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('id')
                ->where('is_page', false)
                ->etc()
        )
        ->json();

    $primaryLanguage = $this->blog->languages[0];

    $variant = PostVariant::where('post_id', $post['id'])
        ->where('language_id', $primaryLanguage->id)
        ->first();

    $this->assertNotNull($variant);
});

it('creates a page', function () {
    $this
        ->callConsoleApi('POST', '/post', ['is_page' => true])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('id')
                ->where('is_page', true)
                ->etc()
        );
});
