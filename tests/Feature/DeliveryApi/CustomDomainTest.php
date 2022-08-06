<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;

it('works with custom domain', function() {

    $content = '<body>{{ _blog.subdomain }}</body>';
    ThemeFilesRepository::createOrUpdateFile(
        BlogService::getBlogBySubdomain('custom'),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content
    );

    $this->get('http://hyvorblogscustom.test')
        ->assertOk()
        ->assertSee('<body>custom</body>', false);

});

it('redirects to homepage if custom domain is not found', function() {

    $this->get('http://someunkowndomain.test')
        ->assertRedirect('https://blogs.hyvor.com');

});