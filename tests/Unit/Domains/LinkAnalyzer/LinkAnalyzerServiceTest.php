<?php declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer;

use App\Domains\LinkAnalyzer\LinkAnalyzerService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Support\Facades\Http;

it('analyzes urls', function() {

    $urls = [
        'https://hyvor.com',
        'https://hyvor.com/blog',
        'https://hyvor.com/blog/hello-world',
        'https://blogs.hyvor.com'
    ];

    Http::fake([
        'https://hyvor.com' => Http::response(null, 200),
        'https://hyvor.com/blog' => Http::response(null, 201),
        'https://hyvor.com/blog/hello-world' => Http::response(null, 301),
        'https://blogs.hyvor.com' => Http::response(null, 500),
    ]);

    $results = LinkAnalyzerService::analyze($urls);

    expect($results)->toBe([
        'https://hyvor.com' => 200,
        'https://hyvor.com/blog' => 201,
        'https://hyvor.com/blog/hello-world' => 301,
        'https://blogs.hyvor.com' => 500,
    ]);

});

it('on failing', function() {

    Http::fake([
        'https://hyvor.com' => fn ($request) => new RejectedPromise(
            new ConnectException('DNS error', $request->toPsrRequest())
        )
    ]);

    $results = LinkAnalyzerService::analyze([
        'https://hyvor.com'
    ]);

    expect($results)->toBe([
        'https://hyvor.com' => 500,
    ]);

});

it('test', function() {

    $results = LinkAnalyzerService::analyze([
        'https://www.grammarly.com'
    ]);

});