<?php

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Mail\LinkAnalyzeReportMail;
use App\Models\LinkAnalyzerCheck;
use Hyvor\Internal\Auth\AuthFake;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Helper\Generator\PostContentGenerator;

it('job works', function () {
    Mail::fake();

    AuthFake::databaseSet([
        ['id' => 12, 'email' => 'test@hyvor.com']
    ]);

    Http::fake([
        'hyvor.com/*' => Http::response('', 200),
        'example.com/*' => Http::response('', 301),
    ]);

    $blog = blogWithLanguageAndRoutes([
        'hyvor_user_id' => 12
    ]);
    $blog->setMeta('link_analysis_email_report', 'always');

    $post = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/about',
            'https://example.com/1',
        ])
    ]);

    addPost($blog, ['is_page' => true], ['status' => PostStatusEnum::PUBLISHED]);

    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    $check = LinkAnalyzerCheck::first();

    expect($check->blog_id)->toBe($blog->id);
    expect($check->posts_count)->toBe(1);
    expect($check->post_variants_count)->toBe(1);
    expect($check->pages_count)->toBe(1);
    expect($check->page_variants_count)->toBe(1);
    expect($check->links_total_count)->toBe(2);
    expect($check->links_ok_count)->toBe(1);
    expect($check->links_broken_count)->toBe(0);
    expect($check->links_redirect_count)->toBe(1);

    Mail::assertSent(LinkAnalyzeReportMail::class, function (LinkAnalyzeReportMail $mail) {
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

    Mail::assertNothingSent();
});

it('does not send mail when broken', function () {
    Mail::fake();

    $blog = blogWithLanguage();
    $blog->setMeta('link_analysis_email_report', 'broken');
    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    Mail::assertNothingSent();
});

it('sends email when broken and there are broken links', function () {
    Http::fake([
        'hyvor.com/*' => Http::response('', 404),
    ]);

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

    Mail::assertSent(LinkAnalyzeReportMail::class);
});
