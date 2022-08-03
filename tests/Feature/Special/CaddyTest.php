<?php

namespace Tests\Feature\Special;

use App\Models\Blog;

it('returns 500 when the custom domain does not exist', function() {

    $this->get('/special/caddy/allowed-domain?domain=hyvor.com')->assertStatus(500);

});

it('returns 200 when the custom is there', function() {

    Blog::factory()->create([
        'hosting_domain' => 'hyvor.com'
    ]);

    $this->get('/special/caddy/allowed-domain?domain=hyvor.com')->assertOk();

});