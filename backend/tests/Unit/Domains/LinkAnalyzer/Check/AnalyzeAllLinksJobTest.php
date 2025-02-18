<?php

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Mail\LinkAnalyzeReportMail;
use App\Models\LinkAnalyzerCheck;
use Hyvor\Internal\Auth\AuthFake;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Helper\Generator\PostContentGenerator;

it('job works', function () {
    Mail::fake();

    AuthFake::databaseSet([
        ['id' => 12, 'email' => 'test@hyvor.com']
    ]);

    $this->app->bind(HttpClientInterface::class, fn() => new MockHttpClient(function ($method, $url, $options) {
        if (str_contains($url, 'hyvor.com')) {
            return new MockResponse(info: ['http_code' => 200]);
        }
        if (str_contains($url, 'example.com')) {
            return new MockResponse(info: ['http_code' => 301]);
        }
        return new MockResponse(info: ['http_code' => 500]);
    }));

    $blog = blogWithLanguageAndRoutes([
        'hyvor_user_id' => 12
    ]);
    $blog->setMeta('link_analysis_email_report', 'always');

    $post = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/about',
            'https://example.com/1',
            'https://invalidwebsite.com'
        ])
    ]);

    addPost($blog, ['is_page' => true], ['status' => PostStatusEnum::PUBLISHED]);

    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    $check = LinkAnalyzerCheck::first();

    expect($check->blog_id)->toBe($blog->id);
    expect($check->posts_count)->toBe(2);
    expect($check->links_total_count)->toBe(3);
    expect($check->links_ok_count)->toBe(1);
    expect($check->links_broken_count)->toBe(0);
    expect($check->links_risky_count)->toBe(1);
    expect($check->links_redirect_count)->toBe(1);

    Mail::assertQueued(LinkAnalyzeReportMail::class, function (LinkAnalyzeReportMail $mail) {
        expect($mail->hasTo('test@hyvor.com'))->toBeTrue();
        return true;
    });
});


it('does not send mail when the option is disabled', function () {
    Mail::fake();

    $blog = blogWithLanguage();
    $blog->setMeta('link_analysis_email_report', 'never');
    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    Mail::assertNothingQueued();
});

it('does not send mail when broken', function () {
    Mail::fake();

    $blog = blogWithLanguage();
    $blog->setMeta('link_analysis_email_report', 'broken');
    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    Mail::assertNothingQueued();
});

it('sends email when broken and there are broken links', function () {
    $this->app->bind(HttpClientInterface::class, fn() => new MockHttpClient(function ($method, $url, $options) {
        return new MockResponse(info: ['http_code' => 404]);
    }));

    Mail::fake();

    $blog = blogWithLanguageAndRoutes();
    $blog->setMeta('link_analysis_email_report', 'broken');

    addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/about',
        ])
    ]);

    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    Mail::assertQueued(LinkAnalyzeReportMail::class);
});
