<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Subscription;
use Hyvor\Internal\Billing\BillingFake;
use Illuminate\Support\Facades\DB;

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
    $time = now();
    $this->travelTo($time);
    BillingFake::enable(license: null);
    $blog = blog([
        'trial_ends_at' => now()->subDay(),
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogscustom.test'
    ]);
    $this->get("http://hyvorblogscustom.test")
        ->assertRedirect('https://blogs.hyvor.com')
        ->assertHeader('X-Blog-Subdomain', $blog->subdomain)
        ->assertHeader('X-Redirect-Reason', 'No license')
        ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');

    $value = DB::table('cache')->where('key', "laravel_cachehas-license:$blog->hyvor_user_id")->first();

    expect(unserialize($value->value))->toBeFalse();
    expect($value->expiration)->toBe($time->addSeconds(30)->getTimestamp());
});