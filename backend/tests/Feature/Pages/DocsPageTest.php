<?php

namespace Tests\Feature\Pages;

it('loads docs', function() {

    $this->get('/docs')
        ->assertStatus(200)
        ->assertSee('<title>Introduction</title>', false);

});