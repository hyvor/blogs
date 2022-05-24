<?php

namespace Tests\Unit\Rules;

use App\Rules\BlogName;

function blogNamePasses(mixed $value) {
    $rule = new BlogName();
    return $rule->passes('name', $value);
}

it('cannot be empty', function() {
    expect(blogNamePasses(null))->toBeFalse();
    expect(blogNamePasses(false))->toBeFalse();
    expect(blogNamePasses(''))->toBeFalse();
});

it('cannot be too long', function() {
    expect(blogNamePasses(str_repeat('h', config('limits.max_blog_name_length') + 1)))->toBeFalse();
    expect(blogNamePasses(str_repeat('h', config('limits.max_blog_name_length') - 1)))->toBeTrue();
});