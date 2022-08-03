<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\RedirectDeleter;
use App\Models\Redirect;

it('deletes redirects', function () {
    $blog = newBlog();

    Redirect::factory()->count(2)->create(['blog_id' => $blog]);

    expect($blog->redirects()->count())->toBe(2);

    (new RedirectDeleter($blog))->delete();

    expect($blog->redirects()->count())->toBe(0);
});

it('does not delete redirects of other blogs', function () {
    Redirect::factory()->count(2)->create(['blog_id' => blog()]);
    (new RedirectDeleter(newBlog()))->delete();
    expect(blog()->redirects()->count())->toBe(2);
});
