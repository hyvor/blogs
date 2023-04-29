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

it('redirects to homepage if the blog is blocked', function() {

    blog([
        'is_blocked' => true,
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);

    $this->get('http://hyvorblogscustom.test')->assertRedirect('https://blogs.hyvor.com');

});


/*it('redirects to homepage if the blog trial is ended', function() {

    $blog = blog([
        'trial_ends_at' => now()->subDay(),
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);
    addPrimaryLanguage($blog);

    $this->get('http://hyvorblogscustom.test')->assertRedirect('https://blogs.hyvor.com');

});*/