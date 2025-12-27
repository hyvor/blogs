<?php

use App\Domains\LinkAnalyzer\Check\FullBlogAnalyzer;
use App\Domains\Post\Content\PostContentService;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\User;
use Hyvor\Internal\Component\Component;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Support\Facades\Route;

Route::get('/api/comms', function () {
    $commsService = app(Hyvor\Internal\Bundle\Comms\CommsService::class);

    $event = new Hyvor\Internal\Bundle\Comms\Event\OrgMigration\InitOrg(1);
    $response = $commsService->send($event, Component::CORE);

    dd($response);
});

Route::get('callout', function () {
    $json = PostContentService::getJsonFromHtml(
        '
        <aside data-emoji="💡" style="background-color: #ffd969" data-fg="#000">The only real valuable thing is intuition.</aside>
    ',
        Blog::find(1)
    );

    dd($json);
});

Route::get('code', function () {
    $languages = Highlighter::highlight(
        code: '$dog = new Dog()',
        language: 'plain',
        themeName: 'nord',
        lineNumbers: true,
        annotations: ''
    );

    dd($languages);
});

Route::get('email', function () {
    return view('emails.trial-ended', [
        'user' => HyvorUser::dummy(),
        'blog' => Blog::first(),
    ]);
});

Route::get('embed', function () {
    $html = '<div id="hyvor-blogs-embed-wrap"></div>';
    $js = view('embed.embed-js', [
        'domain' => 'http://blogs.hyvor.test:8080',
        'subdomain' => 'test'
    ]);
    return $html . '<script>' . $js . '</script>';
});

Route::get('user-email', function () {
    $user = User::whereNotNull('hyvor_user_id')->first();
    UserRepository::sendInviteEmail($user);
});

Route::get('broken', function () {
    sleep(4);
    return 'hello';
});

Route::get('/api/link-report', function () {
    $blog = Blog::first();
    $analyze = new FullBlogAnalyzer($blog);

    return new \App\Domains\LinkAnalyzer\Mail\LinkAnalyzeReportMail($blog, $analyze);
});