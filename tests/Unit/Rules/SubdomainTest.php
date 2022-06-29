<?php

namespace Tests\Unit\Rules;

use App\Rules\Subdomain;

function subdomainPasses(string $value)
{
    $rule = new Subdomain();

    return $rule->passes('subdomain', $value);
}

test('valid', function () {
    expect(subdomainPasses('test'))->toBeTrue();
});

it('can contain hyphens', function () {
    expect(subdomainPasses('test-another'))->toBeTrue();
});

it('can contain numbers and hyphens', function () {
    expect(subdomainPasses('test-123-another'))->toBeTrue();
});

test('invalid chars', function () {
    expect(subdomainPasses('test_another'))->toBeFalse();
});

it('cannot end with hyphen', function () {
    expect(subdomainPasses('test-'))->toBeFalse();
});

it('cannot start with hyphen', function () {
    expect(subdomainPasses('-test'))->toBeFalse();
});


it('does not check unique unless specified explicitly', function () {
    $subdomain = blog()->subdomain;
    expect(subdomainPasses($subdomain))->toBeTrue();
});

it('checks for unique', function () {
    $subdomain = blog()->subdomain;
    $rule = new Subdomain(checkUnique: true);
    $passes = $rule->passes('subdomain', $subdomain);
    expect($passes)->toBeFalse();
});
