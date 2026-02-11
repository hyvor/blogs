<?php

namespace Tests\Feature\DeliveryApi;

use App\Models\Redirect;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('works with subdomain', function () {
    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));
    addRoute($blog, '/');
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())]);

    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);

    $this->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee($content, false);
});

it('works with redirect', function () {
    $blog = blog();
    $redirect = Redirect::factory()->create(['blog_id' => $blog->id]);
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())]);

    $this->get("http://$blog->subdomain.hyvorblogs.io$redirect->path")
        ->assertRedirect($redirect->to);
});

it('redirects to homepage if the blog is blocked', function () {
    $blog = blog(['is_blocked' => true]);
    $this->get("http://$blog->subdomain.hyvorblogs.io/any")->assertRedirect('https://blogs.hyvor.com');
});

/*it('redirects to homepage if the blog trial is ended', function() {

    $blog = blog(['trial_ends_at' => now()->subDay()]);
    $this->get("http://$blog->subdomain.hyvorblogs.io/any")->assertRedirect('https://blogs.hyvor.com');

});*/

it('redirects to other domain if not hosted on subdomain', function () {
    $blog = blog([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogs.com'
    ]);

    $this
        ->get("http://$blog->subdomain.hyvorblogs.io/any")
        ->assertRedirect("https://hyvorblogs.com/any");
});

it('does not redirect if it is disabled', function () {
    $blog = blogWithAccessLanguageAndRoutes([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogs.com',
        'hosting_redirect_subdomain' => false
    ]);
    addThemeTemplateFile($blog, 'hello world');

    $this
        ->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee('hello world');
});

// bug #197
it('redirects to homepage correctly', function () {
    $blog = blog([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogs.com'
    ]);

    $this
        ->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertRedirect("https://hyvorblogs.com");
});

it('redirects on no license', function () {
    $time = now();
    $this->travelTo($time);
    $blog = blog();
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::NONE)]);

    $this->get("http://$blog->subdomain.hyvorblogs.io/any")
        ->assertRedirect('https://blogs.hyvor.com')
        ->assertHeader('X-Blog-Subdomain', $blog->subdomain)
        ->assertHeader('X-Redirect-Reason', 'No license')
        ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');

    $value = DB::table('cache')->where('key', "laravel_cachehas-license-org:$blog->organization_id")->first();

    expect(unserialize($value->value))->toBeFalse();
    expect($value->expiration)->toBe($time->addSeconds(30)->getTimestamp());
});

it('caches for 48 hours when there is a license', function () {
    $time = now();
    $this->travelTo($time);

    $blog = blogWithAccessLanguageAndRoutes();
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())]);

    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);

    $this->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee($content, false);

    dd(DB::table('cache')->get());
    $value = DB::table('cache')->where('key', "laravel_cachehas-license-org:$blog->organization_id")->first();

    expect(unserialize($value->value))->toBeTrue();
    expect($value->expiration)->toBe($time->addHours(48)->getTimestamp());
});

it('does not show trial error for non-default blogs', function () {
    $blog = blog(['type' => 'dev']);
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::NONE)]);
    $this->get("http://$blog->subdomain.hyvorblogs.io/any")
        ->assertDontSee('trial has ended');
});

it('does not fail on internal api call fail', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);
    BillingFake::enable(fn() => throw new CommsApiFailedException('test'));

    $this->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee($content, false);
});

it('gets has license from cache', function () {
    $blog = blogWithAccessLanguageAndRoutes(['organization_id' => 1]);
    Cache::put('has-license-org:1', true, 60);

    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);
    BillingFake::enable(fn() => throw new CommsApiFailedException('test'));

    $this->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee($content, false);
});