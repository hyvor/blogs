<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Domains\Delivery\Twig\DataAPICaller;
use App\Exceptions\TrustedException;
use Mockery\MockInterface;
use Twig\Error\Error;

it('throws an error when endpoint is not set', function () {
    $blogObject = getBlogObject();

    testTwigRendering(
        '{{ data() }}',
        [
            '_blog' => $blogObject
        ],
        ''
    );
})->throws(Error::class);

it('calls the data API', function () {
    $blogObject = getBlogObject();

    $this->mock(
        DataAPICaller::class,
        fn (MockInterface $mock) =>
        $mock
            ->shouldReceive('callApi')
            ->once()
            ->with($blogObject->subdomain, 'blog', [])
    );

    testTwigRendering(
        '{{ data(endpoint="blog") }}',
        [
            '_blog' => $blogObject
        ],
        ''
    );
});

it('calls the data API with arguments', function () {
    $blogObject = getBlogObject();

    $this->mock(
        DataAPICaller::class,
        fn (MockInterface $mock) =>
    $mock
        ->shouldReceive('callApi')
        ->once()
        ->with($blogObject->subdomain, 'other', [
            'val' => 'yes'
        ])
    );

    testTwigRendering(
        '{{ data(endpoint="other",val="yes") }}',
        [
            '_blog' => $blogObject
        ],
        ''
    );
});

it('throws an twig error if the data api throws a trusted exception', function () {
    $blogObject = getBlogObject();

    $this->mock(
        DataAPICaller::class,
        fn (MockInterface $mock) =>
    $mock
        ->shouldReceive('callApi')
        ->once()
        ->andThrow(new TrustedException('OOPS'))
    );

    testTwigRendering(
        '{{ data(endpoint="other",val="yes") }}',
        [
            '_blog' => $blogObject
        ],
        ''
    );
})->throws(Error::class, 'OOPS');

// bug#113
it('works when called two times with joins', function() {

    $blog = blogWithLanguageAndRoutes();
    $blogObject = getBlogObject([], $blog);

    $tag = addTag($blog);

    testTwigRendering(
        <<<TWIG
            {% set posts1 = data(endpoint="posts", filter="tag.slug=$tag->slug") %}
            {% set posts2 = data(endpoint="posts", filter="tag.slug=$tag->slug") %}
            {{ posts1.data|length }} {{ posts2.data|length }}
        TWIG,
        [
            '_blog' => $blogObject
        ],
        '0 0'
    );

});