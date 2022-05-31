<?php

namespace Tests\Unit\Import;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Import\Importer;
use App\Domains\Import\Repository;
use App\Models\Import;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;
use Faker\Factory;
use Mockery;

// php artisan test  --filter 'ImporterTest'

beforeEach(function () {
    $this->repo = new Repository();
    $faker = Factory::create();

    $this->create = Import::factory()->count(1)->create([
        'blog_id' => 1,
        'name' => $faker->name,
        'type' => 'wordpress',
        'status' => 'success',
    ]);

    $this->imports = Mockery::mock('App\Models\Import');
    $this->date = date("Y/m/d h:i:s");
});

it('testing whether the author data has been passed properly to the importer', function () {
    $role = UserRoleEnum::from('editor');
    $status = UserStatusEnum::from('active');

    $this->repo->author(
        id: 1,
        name: 'rasif',
        role: $role,
        status: $status,
        slug: 'rasif',
        email: 'rasif@hyvor.com',
        createdAt: $this->date,
        updatedAt: $this->date,
        pictureUrl: null,
        url: null,
        socialFacebook: null,
        socialTwitter: null,
        socialLinkedin: null,
        socialYoutube: null,
        socialInstagram: null,
        bio: null,
        location: null,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    $this
        ->assertEquals($this->repo->authors, $importer->repository->authors);
});

it('get authors from the repository and save it in the database', function () {
    $role = UserRoleEnum::from('editor');
    $status = UserStatusEnum::from('active');

    $this->repo->author(
        id: 1,
        name: 'rasif',
        role: $role,
        status: $status,
        slug: 'rasif',
        email: 'rasif@hyvor.com',
        createdAt: $this->date,
        updatedAt: $this->date,
        pictureUrl: null,
        url: null,
        socialFacebook: null,
        socialTwitter: null,
        socialLinkedin: null,
        socialYoutube: null,
        socialInstagram: null,
        bio: null,
        location: null,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    foreach ($importer->repository->authors as $author) {
        $user = User::factory()->count(1)->create([
            'created_at' => $author['createdAt'],
            'updated_at' => $author['updatedAt'],
            'blog_id' => $this->blog->id,
            'hyvor_user_id' => 5,
            'picture_url' => $author['pictureUrl'],
            'status' => $author['status'],
            'role' => $author['role'],
            'slug' => $author['slug'],
            'email' => $author['email'],
            'website_url' => $author['url'],
            'social_facebook' => $author['socialFacebook'],
            'social_twitter' => $author['socialTwitter'],
            'social_linkedin' => $author['socialLinkedin'],
            'social_youtube' => $author['socialYoutube'],
            'social_instagram' => $author['socialInstagram'],
        ]);

        $userVariant = UserVariant::factory()->count(1)->create([
            'user_id' => 1,
            'language_id' => 1,
            'name' => $author['name'],
            'bio' => $author['bio'],
            'location' => $author['location'],
        ]);
    }

    $this
        ->assertDatabaseHas('users', [
            'role' => 'editor',
            'status' => 'active',
            'email' => 'rasif@hyvor.com',
        ])
        ->assertDatabaseHas('user_variants', [
            'name' => 'rasif',
        ]);
});

it('testing whether the tag data has been passed properly to the importer', function () {
    $this->repo->tag(
        id: 1,
        name: 'rasif',
        slug: 'test',
        createdAt: $this->date,
        updatedAt: $this->date,
        postsCount: 0,
        codeHead: null,
        codeFoot: null,
        featuredImageUrl: null,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    $this
        ->assertEquals($this->repo->tags, $importer->repository->tags);
});

it('get tags from the repository and save it in the database', function () {
    $this->repo->tag(
        id: 1,
        name: 'rasif',
        slug: 'test',
        createdAt: $this->date,
        updatedAt: $this->date,
        postsCount: 0,
        codeHead: null,
        codeFoot: null,
        featuredImageUrl: null,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    foreach ($importer->repository->tags as $tag) {
        $tagData = Tag::factory()->count(1)->create([
            'created_at' => $tag['createdAt'],
            'updated_at' => $tag['updatedAt'],
            'blog_id' => $this->blog->id,
            'slug' => $tag['slug'],
            'posts_count' => $tag['postsCount'],
            'code_head' => $tag['codeHead'],
            'code_foot' => $tag['codeFoot'],
        ]);

        $tagVariant = TagVariant::factory()->count(1)->create([
            'tag_id' => 1,
            'language_id' => 1,
            'name' => $tag['name'],
            'description' => $tag['description'],
        ]);
    }

    $this
        ->assertDatabaseHas('tags', [
            'slug' => 'test',
        ])
        ->assertDatabaseHas('tag_variants', [
            'name' => 'rasif',
        ]);
});

it('testing whether the post data has been passed properly to the importer', function () {
    $isPage = false;
    $authors = [1];
    $tags = [1];

    $this->repo->post(
        id: 1,
        isPage: $isPage,
        slug: 'hello world',
        status: 'published',
        authors: $authors,
        createdAt: $this->date,
        updatedAt: $this->date,
        publishedAt: $this->date,
        isFeatured: false,
        featuredImageUrl: null,
        canonicalUrl: null,
        codeHead: null,
        codeFoot: null,
        content: 'testing the content',
        title: 'test post',
        description: 'test description',
        tags: $tags,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    $this
        ->assertEquals($this->repo->posts, $importer->repository->posts);
});

it('get posts from the repository and save it in the database', function () {
    $isPage = false;
    $authors = [1];
    $tags = [1];

    $this->repo->post(
        id: 1,
        isPage: $isPage,
        slug: 'hello world',
        status: 'published',
        authors: $authors,
        createdAt: $this->date,
        updatedAt: $this->date,
        publishedAt: $this->date,
        isFeatured: false,
        featuredImageUrl: null,
        canonicalUrl: null,
        codeHead: null,
        codeFoot: null,
        content: 'testing the content',
        title: 'test post',
        description: 'test description',
        tags: $tags,
    );

    $importer = new Importer($this->repo, blog(), $this->imports);

    foreach ($importer->repository->posts as $post) {
        // dd($post['status']);
        $post = Post::factory()->count(1)->create([
            'created_at' => $post['createdAt'],
            'updated_at' => $post['updatedAt'],
            'published_at' => $post['publishedAt'],
            'blog_id' => $this->blog->id,
            'is_page' => $isPage,
            'is_featured' => $post['isFeatured'],
            'slug' => $post['slug'],
            'featured_image_url' => $post['featuredImageUrl'],
            'canonical_url' => $post['canonicalUrl'],
            'code_head' => $post['codeHead'],
            'code_foot' => $post['codeFoot'],
        ]);

        // PostVariant::factory()->count(1)->create([
        //     'post_id' => 1,
        //     'language_id' => 1,
        //     'status' => 'published',
        //     'content' => 'hello tets',
        //     'title' => 'post title',
        //     'description' => 'test description',
        // ]);
    }

    $this
        ->assertDatabaseHas('posts', [
            'slug' => 'hello world',
        ]);
});
