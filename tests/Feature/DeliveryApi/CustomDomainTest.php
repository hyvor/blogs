<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;

it('works with custom domain', function () {

    $blog = blog([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);
    addBlogVariants($blog, addPrimaryLanguage($blog));
    addRoute($blog, '/');

    $content = '<body>{{ _blog.subdomain }}</body>';
    addThemeTemplateFile($blog, $content);

    $this->get('http://hyvorblogscustom.test')
        ->assertOk()
        ->assertSee("<body>$blog->subdomain</body>", false);

});

it('redirects to homepage if custom domain is not found', function () {
    $this->get('http://someunkowndomain.test')
        ->assertRedirect('https://blogs.hyvor.com');
});
