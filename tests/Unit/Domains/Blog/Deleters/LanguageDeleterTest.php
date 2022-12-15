<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\LanguageDeleter;

it('deletes languages', function () {
    $blog = blog();
    addPrimaryLanguage($blog);
    addLanguage($blog);

    expect($blog->languages()->count())->toBe(2);

    $deleter = new LanguageDeleter($blog);
    $deleter->delete();

    expect($blog->languages()->count())->toBe(0);
});
