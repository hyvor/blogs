<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Models\Language;
use Illuminate\Support\Facades\Queue;

it('deletes a language', function() {

    Queue::fake();

    $language = Language::factory()->create(['code' => 'si', 'blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/language/$language->id")
        ->assertOk();

    Queue::assertPushed(DeleteLanguageVariants::class);

});

it('does not delete the primary language', function() {

    $language = blog()->languages[0];

    $this->callConsoleApi('DELETE', "/language/$language->id")
        ->assertUnprocessable()
        ->assertSee(['Primary', 'cannot']);

});