<?php

namespace Tests\Unit\Rules;

use App\Rules\BlogDescription;

function descriptionPasses(mixed $value)
{
    $rule = new BlogDescription();

    return $rule->passes('description', $value);
}

it('can be null or empty string', function () {
    expect(descriptionPasses(null))->toBeTrue();
    expect(descriptionPasses(false))->toBeFalse();
});

it('cannot be too long', function () {
    expect(
        descriptionPasses(str_repeat('h', config('limits.max_blog_description_length') + 1))
    )->toBeFalse();
});

it('can be any string within length', function () {
    expect(
        descriptionPasses(str_repeat('h', config('limits.max_blog_description_length')))
    )->toBeTrue();
});
