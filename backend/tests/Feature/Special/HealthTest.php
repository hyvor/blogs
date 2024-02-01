<?php

namespace Tests\Feature\Special;

it('should return ok', function() {
    $this->call('GET', '/api/special/health')
        ->assertOk()
        ->assertSee('ok');
});