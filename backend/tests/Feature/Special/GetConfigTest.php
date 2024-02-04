<?php

namespace Tests\Feature\Special;


it('gets config', function() {

    $this->call('GET', '/api/special/config')
        ->assertJsonPath('domains.app', 'blogs.hyvor.com')
        ->assertJsonPath('domains.delivery', 'hyvorblogs.io');

});