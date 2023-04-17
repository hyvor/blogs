<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Ai;

use App\Domains\Post\Content\PostContentRepository;
use Illuminate\Support\Facades\Http;

it('translates', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([
            'translations' => [
                ['text' => 'Bonjour le monde'],
                ['text' => '<p>tester ce système</p>']
            ]
        ]),
    ]);

    $blog = blogWithAccess();

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => PostContentRepository::generateParagraph('Testing this system'),
        'title' => 'Hello World'
    ])
        ->assertOk()
        ->assertJsonPath('title', 'Bonjour le monde')
        ->assertJsonPath('content', PostContentRepository::generateParagraph('tester ce système'));

});

it('translates with code block', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([
            'translations' => [
                ['text' => ''],
                ['text' => '<p>Bonjour</p><pre data-language="php" data-annotations="h=1" data-name="index.php"><code>0</code></pre>']
            ]
        ]),
    ]);

    $blog = blogWithAccess();

    $codeBlock = [
        'type' => 'code_block',
        'attrs' => [
            'language' => 'php',
            'name' => 'index.php',
            'annotations' => 'h=1',
        ],
        'content' => [['type' => 'text', 'text' => 'x = 10']]
    ];

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => json_encode([
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hi']]],
                $codeBlock
            ]
        ]),
        'title' => ''
    ])
        ->assertOk()
        ->assertJsonPath('title', '')
        ->assertJsonPath('content', json_encode([
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Bonjour']]],
                $codeBlock
            ]
        ]));

});

it('throws API error', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([], 500),
    ]);

    $blog = blogWithAccess();

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => PostContentRepository::generateParagraph('Testing this system'),
        'title' => 'Hello World'
    ])
        ->assertUnprocessable()
        ->assertSee('DeepL API error: status code 500');

});