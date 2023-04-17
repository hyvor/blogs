<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Ai;

use App\Domains\Post\Content\PostContentRepository;

it('translates', function() {

    $blog = blogWithAccess();

    $content = file_get_contents(__DIR__ . '/content.json');

    consoleApi($blog, 'post', '/ai/translate', [
        'source_lang' => 'EN',
        'target_lang' => 'FR',
        'content' => $content,
        'title' => 'What is up?'
    ])->dd();

});