<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Shared\Count\AuthorCountsJob;

it('counts author posts', function() {

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    $job = new AuthorCountsJob(blog());
    $job->handle();

});