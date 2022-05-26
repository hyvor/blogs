<?php

namespace Tests\Feature\ConsoleAPI\Redirect;

use App\Models\Redirect;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a redirect', function() {

    $redirect = Redirect::factory()->create(['blog_id' => blog()]);

    $path = '/example';
    $to = 'https://example.com';
    $type = 'temporary';

    $this->callConsoleApi('PUT', "/redirect/$redirect->id", [
        'path' => $path,
        'to' => $to,
        'type' => $type
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('path', $path)
                ->where('to', $to)
                ->where('type', $type)
                ->etc()
        );

});


it('does not create a redirect when path is taken', function() {

    $path = '/example';
    $to = 'https://example.com';
    $type = 'permanent';

    $r1 = Redirect::factory()->create(['blog_id' => blog(), 'path' => $path]);
    $r2 = Redirect::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('PUT', "/redirect/$r2->id", [
        'path' => $path,
        'to' => $to,
        'type' => $type
    ])
        ->assertUnprocessable()
        ->assertSee(['path', 'exists']);

});