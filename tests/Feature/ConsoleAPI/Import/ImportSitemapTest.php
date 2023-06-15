<?php

namespace Tests\Feature\ConsoleAPI\Import;

use App\Data\Enums\ImportTypeEnum;
use App\Data\Enums\JobStatusEnum;
use App\Domains\Import\Importer\ImportJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('test works', function() {

    Http::fake([
        'https://example.com/blog/post' => Http::response(
<<<HTML
<html>
    <meta property="og:image" content="https://example.com/image.png" />
    <h1>Post Title</h1>
    <p class="description">My description</p>
    <article>
        <p>This is a test post</p>
        <blockquote>
            Rejected
        </blockquote>
    </article>
    <time>2023-04-05</time>
</html>
HTML)
    ]);

    $blog = blogWithAccess();

    consoleApi($blog, 'POST', '/data/import/sitemap/test', [
        'url' => 'https://example.com/blog/post',
        'slug_exclude' => '/blog',
        'css' => [
            'title' => 'h1',
            'description' => 'p.description',
            'content' => 'article',
            'content_exclude' => 'blockquote',
            'published_date' => 'time'
        ]
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Post Title')
        ->assertJsonPath('data.description', 'My description')
        ->assertJsonPath('data.content_html', '<p>This is a test post</p>')
        ->assertJsonPath('data.published_at', Carbon::parse('2023-04-05')->getTimestamp())
        ->assertJsonPath('data.featured_image_url', 'https://example.com/image.png')
        ->assertJsonPath('data.slug', 'post')
        ->assertJsonPath('meta.select_type.title', 'css_selector')
        ->assertJsonPath('meta.select_type.description', 'css_selector')
        ->assertJsonPath('meta.select_type.published_at', 'css_selector');

});

it('test handles errors', function() {

    Http::fake(['https://example.com/blog/post' => Http::response([], 404)]);

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/data/import/sitemap/test', [
        'url' => 'https://example.com/blog/post',
        'css' => ['content' => 'article',]
    ])
        ->assertUnprocessable()
        ->assertSee('cannot_fetch');

});

it('imports sitemap calls the job', function() {

    Queue::fake();

    $blog = blogWithAccess();

    consoleApi($blog, 'POST', '/data/import/sitemap/import', [
        'sitemap_url' => 'https://example.com/sitemap.xml',
        'css' => ['content' => 'article',]
    ])
        ->assertOk();

    Queue::assertPushed(ImportJob::class, function (ImportJob $job) {

        expect($job->import->type)->toBe(ImportTypeEnum::SITEMAP);
        expect($job->import->name)->toBe('https://example.com/sitemap.xml');
        expect($job->import->status)->toBe(JobStatusEnum::PENDING);
        expect($job->importImages)->toBeFalse();

        return true;
    });

});