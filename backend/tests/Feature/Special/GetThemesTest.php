<?php

namespace Tests\Feature\Special;

use App\Models\Theme;

it('gets themes', function() {

    Theme::factory()->count(3)->create();

    $this->call('GET', '/api/special/themes')
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJsonStructure([
            '*' => [
                'id',
                'type',
                'name',
                'latest_version',
                'preview_subdomain'
            ]
        ]);

});