<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Misc;

it('gets prosemirror json', function() {

    $blog = blogWithAccess();

    consoleApi($blog, 'GET', '/misc/prosemirror/json', [
        'html' => '<p>Hello World</p>'
    ])
        ->assertOk()
        ->assertJsonPath('json', json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello World'
                        ]
                    ]
                ]
            ]
        ]));

});