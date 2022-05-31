<?php

namespace Tests\Unit\Domains\User;

use App\Domains\User\UniqueSlugGenerator;
use App\Models\User;
use Illuminate\Support\Str;

it('generates a slug for hyvor users', function() {

    $hyvorUser = hyvorUser();
    $slug = UniqueSlugGenerator::forHyvorUser(blog(), $hyvorUser);

    expect($slug)->toBe(Str::slug($hyvorUser->name));

});

it('alternates to username when name is taken (hyvor users)', function() {

    $hyvorUser = hyvorUser();
    $blog = blog();

    User::factory()->create([
        'blog_id' => $blog->id,
        'slug' => Str::slug($hyvorUser->name)
    ]);

    $slug = UniqueSlugGenerator::forHyvorUser($blog, $hyvorUser);

    expect($slug)->toBe(Str::slug($hyvorUser->username));

});

it('alternates to email when name and username is taken (hyvor users)', function() {

    $hyvorUser = hyvorUser();
    $blog = blog();

    User::factory()->create([
        'blog_id' => $blog->id,
        'slug' => Str::slug($hyvorUser->name)
    ]);

    User::factory()->create([
        'blog_id' => $blog->id,
        'slug' => Str::slug($hyvorUser->username)
    ]);

    $slug = UniqueSlugGenerator::forHyvorUser($blog, $hyvorUser);

    expect($slug)->toBe(Str::slug($hyvorUser->email));

});

