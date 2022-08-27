<?php

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;

it('does not render blogs if embeddable is not set', function () {
    $this->call('GET', '/embed/iframe/test', ['url' => 'https://example.org'])
        ->assertUnprocessable()
        ->assertSee('Not embeddable');
});

it('returns HTML response', function () {
    $blog = blog();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        <<<HTML
        <html>
            <head></head>
            <body>Testing</body>
        </html>
        HTML
    );

    $blog->setMeta('embeddable', true);
    $blog->setMeta('embedding_domains', 'example.org');

    $this->call('GET', '/embed/iframe/test', ['url' => 'https://example.org'])
        ->assertOk()
        ->assertSee('<script>', false); // HTMLProcessor adds it (it is separately tested)
});
