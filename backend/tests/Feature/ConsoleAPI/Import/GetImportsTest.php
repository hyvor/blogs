<?php

namespace Tests\Feature\ConsoleAPI\Import;

use App\Models\Import;

it('gets imports', function() {

    $blog = blogWithAccess();
    Import::factory()->count(3)->create([
        'blog_id' => $blog
    ]);

    Import::factory()->create();

    consoleApi($blog, 'GET', '/data/imports')
        ->assertOk()
        ->assertJsonCount(3);

});