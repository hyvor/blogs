<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Models\Redirect;
use Illuminate\Testing\Fluent\AssertableJson;

it('deletes', function() {

    $redirect = Redirect::factory()->create(['blog_id' => blog()]);

    $this
        ->callConsoleApi('DELETE', "/redirect/$redirect->id")
        ->assertOk();

    expect(Redirect::find($redirect->id))->toBeNull();

});