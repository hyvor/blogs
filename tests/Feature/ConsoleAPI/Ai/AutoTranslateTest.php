<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Ai;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Models\AutoTranslation;
use Illuminate\Support\Facades\Http;
use Tests\Helper\Generator\PostContentGenerator;

it('translates', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([
            'translations' => [
                ['text' => 'Bonjour le monde'],
                ['text' => 'Bienvenue sur HYVOR'],
                ['text' => '<p>tester ce système</p>']
            ]
        ]),
    ]);

    $blog = blogWithAccess();
    createSubscription($blog, SubscriptionPlanEnum::GROWTH);

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => PostContentGenerator::generateParagraph('Testing this system'),
        'title' => 'Hello World',
        'description' => 'Welcome to HYVOR'
    ])
        ->assertOk()
        ->assertJsonPath('title', 'Bonjour le monde')
        ->assertJsonPath('description', 'Bienvenue sur HYVOR')
        ->assertJsonPath('content', PostContentGenerator::generateParagraph('tester ce système'));

    $autoTranslation = AutoTranslation::where('blog_id', $blog->id)->first();

    expect($autoTranslation->source_lang)->toBe(DeepLSourceLangEnum::EN);
    expect($autoTranslation->target_lang)->toBe(DeepLTargetLangEnum::FR);
    expect($autoTranslation->chars)->toBe(46); // html without tags + title + description length


});

it('translates with code block', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([
            'translations' => [
                ['text' => ''],
                ['text' => ''],
                ['text' => '<p>Bonjour</p><pre data-language="php" data-annotations="h=1" data-name="index.php"><code>0</code></pre>']
            ]
        ]),
    ]);

    $blog = blogWithAccess();
    createSubscription($blog, SubscriptionPlanEnum::GROWTH);

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

// #190
it('translates with code block with HTML', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([
            'translations' => [
                ['text' => ''],
                ['text' => ''],
                ['text' => '<pre data-language="php" data-annotations="h=1" data-name="index.php"><code>&lt;html&gt;&lt;/html&gt;</code></pre>']
            ]
        ]),
    ]);

    $blog = blogWithAccess();
    createSubscription($blog, SubscriptionPlanEnum::GROWTH);

    $codeBlock = [
        'type' => 'code_block',
        'attrs' => [
            'language' => 'php',
            'name' => 'index.php',
            'annotations' => 'h=1',
        ],
        'content' => [['type' => 'text', 'text' => '<html></html>']]
    ];

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => json_encode([
            'type' => 'doc',
            'content' => [
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
                $codeBlock
            ]
        ]));

});

it('throws API error', function() {

    Http::fake([
        'https://api.deepl.com/v2/translate' => Http::response([], 500),
    ]);

    $blog = blogWithAccess();
    createSubscription($blog, SubscriptionPlanEnum::GROWTH);

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => PostContentGenerator::generateParagraph('Testing this system'),
        'title' => 'Hello World'
    ])
        ->assertUnprocessable()
        ->assertSee('DeepL API error: status code 500');

});

it('throws an error when limits reached', function() {

    $blog = blogWithAccess();
    createSubscription($blog, SubscriptionPlanEnum::GROWTH);

    AutoTranslation::create([
        'blog_id' => $blog->id,
        'source_lang' => DeepLSourceLangEnum::EN,
        'target_lang' => DeepLTargetLangEnum::FR,
        'chars' => 100000000,
    ]);

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => PostContentGenerator::generateParagraph('Testing this system'),
        'title' => 'Hello World'
    ])
        ->assertUnprocessable()
        ->assertSee('This blog has reached the limit of auto-translations for this month');

});