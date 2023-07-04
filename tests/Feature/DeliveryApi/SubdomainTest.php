<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Redirect;

it('works with subdomain', function () {

    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));
    addRoute($blog, '/');

    $content = '<body>Testing</body>';
    addThemeTemplateFile($blog, $content);

    $this->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertOk()
        ->assertSee($content, false);
});

it('works with redirect', function () {

    $blog = blog();
    $redirect = Redirect::factory()->create(['blog_id' => $blog->id]);

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

it('redirects to other domain if not hosted on subdomain', function() {

    $blog = blog([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogs.com'
    ]);

    $this
        ->get("http://$blog->subdomain.hyvorblogs.io/any")
        ->assertRedirect("https://hyvorblogs.com/any");

});

// bug #197
it('redirects to homepage correctly', function() {

    $blog = blog([
        'hosting_at' => 'domain',
        'hosting_domain' => 'hyvorblogs.com'
    ]);

    $this
        ->get("http://$blog->subdomain.hyvorblogs.io")
        ->assertRedirect("https://hyvorblogs.com");

});