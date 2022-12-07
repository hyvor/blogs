<?php

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;

it('does not render blogs if embeddable is not set', function () {
    $blog = blog();

    $this->call('GET', '/embed/iframe/' . $blog->subdomain, ['url' => 'https://example.org'])
        ->assertUnprocessable()
        ->assertSee('Not embeddable');
});

it('returns HTML response', function () {
    $blog = blog();
    addPrimaryLanguage($blog);
    addRoute($blog, '/');

    addThemeTemplateFile($blog, <<<HTML
        <html>
            <head></head>
            <body>Testing</body>
        </html>
    HTML);

    $blog->setMeta('embeddable', true);
    $blog->setMeta('embedding_domains', 'example.org');

    $this->call('GET', "/embed/iframe/$blog->subdomain", ['url' => 'https://example.org'])
        ->assertOk()
        ->assertSee('<script>', false); // HTMLProcessor adds it (it is separately tested)
});
