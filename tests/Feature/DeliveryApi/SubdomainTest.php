<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Redirect;

it('works with subdomain', function () {
    $content = '<body>Testing</body>';
    ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content
    );

    $this->get('http://test.hyvorblogs.test')
        ->assertOk()
        ->assertSee($content, false);
});

it('works with redirect', function () {
    $redirect = Redirect::factory()->create(['blog_id' => blog()]);

    $this->get("http://test.hyvorblogs.test$redirect->path")
        ->assertRedirect($redirect->to);
});
