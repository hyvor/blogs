<?php

use App\Domains\Post\Content\PostContentService;
use App\Domains\Integrations\Shopify\ShopifyService;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\ShopifyShop;
use App\Models\User;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Support\Facades\Route;

Route::get('callout', function () {
    $json = PostContentService::getJsonFromHtml('
        <aside data-emoji="💡" style="background-color: #ffd969" data-fg="#000">The only real valuable thing is intuition.</aside>
    ', Blog::find(1));

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
    return view('emails.invite-user', [
        'hyvorUser' => HyvorUser::dummy(),
        'user' => User::first(),
        'blog' => Blog::first(),
        'link' => '',
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


Route::get('shopify', function() {

    $service = new ShopifyService();
    dd($service->getShopUrl(ShopifyShop::first()));

});


Route::get('user-email', function() {

    $user = User::whereNotNull('hyvor_user_id')->first();
    UserRepository::sendInviteEmail($user);

});