<?php

namespace Tests\Feature\CliAPI;

it('requires a valid subdomain', function () {
    $this->callCliApi('PATCH', '/files',  [], 'invalid-domain')
        ->assertUnprocessable()
        ->assertSee(['Invalid', 'subdomain']);
});

it('requires a DEV blog', function () {
    $this->callCliApi('PATCH', '/files',  [],   config('test.subdomain'))
        ->assertUnprocessable()
        ->assertSee(['use', 'DEV blog']);
});
