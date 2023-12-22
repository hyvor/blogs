<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Redirect;
use Illuminate\Support\Facades\Event;

it('deletes', function () {
    Event::fake();

    $blog = blogWithAccess();

    $redirect = Redirect::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/redirect/$redirect->id")
        ->assertOk();

    expect(Redirect::find($redirect->id))->toBeNull();

    Event::assertDispatched(RedirectChangedEvent::class);
});
