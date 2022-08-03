<?php

namespace Tests\Feature\DeliveryApi\PathMatcher;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\PostPreviewSecretEncryptor;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Post;

beforeEach(function () {
    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        '{{ _post.id }}{{ _lang.code }}',
    );
});

it('matches preview page', function () {
    $post = Post::where('blog_id', $this->blog->id)->first();

    $language = $this->blog->languages[0];
    $id = PostPreviewSecretEncryptor::getPreviewSecret($post);

    $pathMatcher = new PathMatcher($this->blog, "/p/$id/$language->code");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe($post->id . $language->code);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});

it('language works', function () {
    $post = Post::where('blog_id', $this->blog->id)->first();

    $language = $this->blog->languages[1];
    $id = PostPreviewSecretEncryptor::getPreviewSecret($post);

    $pathMatcher = new PathMatcher($this->blog, "/p/$id/$language->code");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe($post->id . $language->code);
});

it('displays unsaved content HTML if it is there', function () {
    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        '{{ _post.content | raw }}',
    );

    $language = $this->blog->languages[0];

    $post = Post::where('blog_id', $this->blog->id)->first();
    $content = PostContentRepository::generateRandom();
    $post->variants->firstWhere('language_id', $language->id)->update(['content_unsaved' => $content]);

    $id = PostPreviewSecretEncryptor::getPreviewSecret($post);

    $pathMatcher = new PathMatcher($this->blog, "/p/$id/$language->code");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe(PostContentRepository::getHtml($content, $this->blog));
});
