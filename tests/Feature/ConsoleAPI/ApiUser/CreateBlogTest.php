<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('validates', function () {
    $this->callConsoleUserApi('POST', '/blog')
        ->assertUnprocessable()
        ->assertSee(['name', 'required']);

    $this->callConsoleUserApi('POST', '/blog', ['name' => 'test'])
        ->assertUnprocessable()
        ->assertSee(['subdomain', 'required']);
});

it('creates a blog', function () {

    $blogId = $this->callConsoleUserApi('POST', '/blog', [
        'name' => 'Testing',
        'subdomain' => 'new-blog',
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('blog')
                ->has(
                    'user',
                    fn (AssertableJson $json) =>
                    $json->where('role', UserRoleEnum::OWNER->value)
                        ->etc()
                )
        )->json()['blog']['id'];

    $blog = Blog::find($blogId);

    expect($blog->subdomain)->toBe('new-blog');
    expect($blog->type)->toBe(BlogTypeEnum::DEFAULT);
    expect(BlogVariant::where('blog_id', $blogId)->count())->toBe(1);
});

it('creates a dev blog', function () {
    $blogId = $this->callConsoleUserApi('POST', '/blog', [
        'name' => 'Testing',
        'is_dev' => true,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
        $json->has('blog')
            ->has(
                'user',
                fn (AssertableJson $json) =>
            $json->where('role', UserRoleEnum::OWNER->value)
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
    $blog = Blog::first();

    $this
        ->callConsoleUserApi('POST', '/blog', [
            'name' => 'Testing',
            'subdomain' => $blog->subdomain,
        ])
        ->assertUnprocessable()
        ->assertSee(['Subdomain', 'taken']);
});
