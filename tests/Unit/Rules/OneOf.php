<?php

namespace Tests\Unit\Rules;

use App\Rules\OneOf;
use App\Rules\RedirectPath;

function oneOfPasses(string $value, array $of) {
    $rule = new OneOf(...$of);
    return $rule->passes('name', $value);
}

test('validation', function() {
    expect(oneOfPasses('string', ['string']))->toBeTrue();
    expect(oneOfPasses('string', ['integer']))->toBeFalse();
    expect(oneOfPasses('string', ['string', 'integer']))->toBeTrue();
    expect(oneOfPasses('/hello-world', ['url', new RedirectPath]))->toBeTrue();
    expect(oneOfPasses('https://example.com', ['url', new RedirectPath]))->toBeTrue();
    expect(oneOfPasses('lsefklsf', ['url', new RedirectPath]))->toBeFalse();
});