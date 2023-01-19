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