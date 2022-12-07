<?php

namespace Tests\Feature\CliAPI;

it('requires a valid subdomain', function () {
    $this->callCliApi('invalid-domain', 'PATCH', '/files', [])
        ->assertUnprocessable()
        ->assertSee(['Invalid', 'subdomain']);
});

it('requires a DEV blog', function () {
    $blog = blog();
    $this->callCliApi($blog, 'PATCH', '/files', [])
        ->assertUnprocessable()
        ->assertSee(['use', 'DEV blog']);
});