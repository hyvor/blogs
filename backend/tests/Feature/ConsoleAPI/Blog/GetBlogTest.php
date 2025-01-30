<?php

namespace Tests\Feature\ConsoleAPI\Blog;


it('gets blog', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);

    consoleApi($blog, 'GET', '/blog')
        ->assertOk()
        ->assertJsonPath('blog.subdomain', $blog->subdomain)
        ->assertJsonIsObject('counts')
        ->assertJsonIsArray('users')
        ->assertJsonIsArray('tags')
        ->assertJsonIsArray('languages');

});
