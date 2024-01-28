<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {

    // themes are needed to create a blog
    Theme::factory()
        ->count(2)
        ->state(new Sequence(
            ['name' => 'hello'],
            ['name' => 'blank']
        ))
        ->has(ThemeVersion::factory(), 'versions')
        ->create();
});

it('validates', function () {
    consoleUserApi('POST', '/blog')
        ->assertUnprocessable()
        ->assertSee(['name', 'required']);

    consoleUserApi('POST', '/blog', ['name' => 'test'])
        ->assertUnprocessable()
        ->assertSee(['subdomain', 'required']);
});

it('creates a blog', function () {
    $blogId = consoleUserApi('POST', '/blog', [
        'name' => 'Testing',
        'subdomain' => 'new-blog',
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('blog')
                ->has(
                    'user',
                    fn (AssertableJson $json) => $json->where('role', UserRoleEnum::OWNER->value)
                        ->etc()
                )
        )->json()['blog']['id'];

    $blog = Blog::find($blogId);

    expect($blog->subdomain)->toBe('new-blog');
    expect($blog->ip)->toBe('127.0.0.1');
    expect($blog->type)->toBe(BlogTypeEnum::DEFAULT);
    expect(BlogVariant::where('blog_id', $blogId)->count())->toBe(1);
});

it('creates a dev blog', function () {
    $blogId = consoleUserApi('POST', '/blog', [
        'name' => 'Testing',
        'is_dev' => true,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('blog')
            ->has(
                'user',
                fn (AssertableJson $json) => $json->where('role', UserRoleEnum::OWNER->value)
                ->etc()
            )
        )->json()['blog']['id'];

    $blog = Blog::find($blogId);

    expect($blog->type)->toBe(BlogTypeEnum::DEV);
    // https://gist.github.com/johnelliott/cf77003f72f889abbc3f32785fa3df8d
    expect($blog->subdomain)->toMatch(
        '/^dev-[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i'
    );
});

it('cant create a blog with already existing subdomain', function () {
    $blog = blogWithAccess();

    consoleUserApi('POST','/blog', [
            'name' => 'Testing',
            'subdomain' => $blog->subdomain,
        ])
        ->assertUnprocessable()
        ->assertSee(['Subdomain', 'taken']);
});

it('does not create if the user has 2 blogs without subscription', function() {

    blogWithAccess();
    blogWithAccess();

    consoleUserApi('POST','/blog', [
        'name' => 'Testing',
        'subdomain' => 'some-subdomain'
    ])
        ->assertUnprocessable()
        ->assertSee('Please upgrade at least one of your blogs to create more');

});

it('allows when you have a subscriptoin', function() {

    $blog1 = blogWithAccess();
    createSubscription($blog1);
    blogWithAccess();

    consoleUserApi('POST','/blog', [
        'name' => 'Testing',
        'subdomain' => 'some-subdomain'
    ])
        ->assertOk();

});