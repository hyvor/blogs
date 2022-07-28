<?php

namespace Tests\Feature\ConsoleAPI\Routes;

use App\Models\Route;

it('deletes a route', function() {

    $route = Route::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/route/$route->id");

    expect(Route::find($route->id))->toBeNull();

});