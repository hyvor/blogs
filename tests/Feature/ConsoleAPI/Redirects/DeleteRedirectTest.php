<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Models\Redirect;

it('deletes', function () {
    $redirect = Redirect::factory()->create(['blog_id' => blog()]);

    $this
        ->callConsoleApi('DELETE', "/redirect/$redirect->id")
        ->assertOk();

    expect(Redirect::find($redirect->id))->toBeNull();
});
