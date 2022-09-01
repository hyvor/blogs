<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('searches tags', function() {

    $name = 'Thisisname';
    $languageId = blog()->languages[0]->id;

    Tag::factory()
        ->has(
            TagVariant::factory()->state([
                'language_id' => $languageId,
                'name' => $name,
            ]),
            'variants'
        )->create(['blog_id' => blog()]);

    $this->callConsoleApi('GET', '/tags/search', [
        'search' => 'Thisis',
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->count(1)
                ->where('0.variants.0.name', $name)
        );

});