<?php

namespace Tests\Unit\Rules;

use App\Rules\BlogHostingDomain;

function hostingDomainPasses(mixed $value)
{
    $rule = new BlogHostingDomain();

    return $rule->passes('hosting_domain', $value);
}

it('passes on null', function () {
    expect(hostingDomainPasses(null))->toBeTrue();
});

it('passes on correct domain names', function () {
    expect(hostingDomainPasses('blog.hyvor.com'))->toBeTrue();
    expect(hostingDomainPasses('hyvor.com'))->toBeTrue();
    expect(hostingDomainPasses('hyvor'))->toBeTrue();
    expect(hostingDomainPasses('hyv-or.com'))->toBeTrue();
    expect(hostingDomainPasses('hyvor.com.uk'))->toBeTrue();
    expect(hostingDomainPasses('hyvor99.com'))->toBeTrue();
    expect(hostingDomainPasses('hyvor.co.in'))->toBeTrue();
    expect(hostingDomainPasses('www.hyvor.com'))->toBeTrue();
    expect(hostingDomainPasses('xn--masekowski-d0b.pl'))->toBeTrue();
    expect(hostingDomainPasses('xn--fiqa61au8b7zsevnm8ak20mc4a87e.xn--fiqs8s'))->toBeTrue();
});

it('fails on wrong domain names', function () {
    expect(hostingDomainPasses('https://hyvor.com'))->toBeFalse();
    expect(hostingDomainPasses('name@hyvor.com'))->toBeFalse();
    expect(hostingDomainPasses('@!)(DXN!LK21m3'))->toBeFalse();
    expect(hostingDomainPasses(false))->toBeFalse();
});
