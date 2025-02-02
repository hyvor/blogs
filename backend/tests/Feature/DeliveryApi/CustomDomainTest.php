<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Subscription;

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

it('redirects to homepage if the blog is blocked', function () {
    blog([
        'is_blocked' => true,
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);

    $this->get('http://hyvorblogscustom.test')->assertRedirect('https://blogs.hyvor.com');
});


it('shows error when trial has ended', function () {
    $blog = blog([
        'trial_ends_at' => now()->subDay(),
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);
    $this->get("http://hyvorblogscustom.test")
        ->assertSee('trial has ended');
})->skip('Currently the trial is not checked, so skipped');

it('does now show an error when trial is ended but there is a subscription', function () {
    $blog = blogWithAccessLanguageAndRoutes([
        'trial_ends_at' => now()->subDay(),
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);
    Subscription::factory()->create(['blog_id' => $blog]);

    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);

    $this->get("http://hyvorblogscustom.test")
        ->assertOk()
        ->assertSee($content, false);
});
