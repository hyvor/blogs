<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\ThemeDeleter;
use App\Models\ThemeFile;

it('deletes theme files', function() {

    $blog = newBlog();
    $blog2 = newBlog();

    ThemeFile::factory()->count(3)->create(['blog_id' => $blog]);
    ThemeFile::factory()->count(1)->create(['blog_id' => $blog2]);

    (new ThemeDeleter($blog))->delete();

    expect($blog->themeFiles()->count())->toBe(0);
    expect($blog2->themeFiles()->count())->toBe(1); // has not deleted other

});