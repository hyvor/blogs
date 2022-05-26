<?php

namespace Tests\Unit\Rules;

use App\Models\Redirect;
use App\Rules\RedirectPath;

function pathPasses($val) {
    $rule = new RedirectPath(blog());
    return $rule->passes('path', $val);
}

test('validation', function() {
    expect(pathPasses('/hello-world'))->toBe(true);
    expect(pathPasses('hello-world'))->toBe(false);
    expect(pathPasses('/hello world'))->toBe(false);
    expect(pathPasses('/hel@@DCOWMao3f'))->toBe(true);
});

it('does not pass when path is already there', function() {
    $path = '/new';
    Redirect::factory()->create(['blog_id' => blog(), 'path' => $path]);

    expect(pathPasses('/news'))->toBeTrue();
    expect(pathPasses($path))->toBeFalse();
});