<?php

namespace Tests\Feature\DeliveryApi\Embed;

it('returns javascript', function() {

    $this->get('/embed/embed.js?subdomain=test')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertSee('window.HYVOR_BLOGS_EMBED_SUBDOMAIN = "test"', false);

});