<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Api\ApiKeysRepository;
use App\Domains\Cache\CacheService;
use App\Domains\Delivery\PostPreviewSecretEncryptor;
use App\Domains\Language\LanguageRepository;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Testing\Fluent\AssertableJson;

it('requires a valid  API key', function () {
    $blog = blog();

    $this->get("/api/delivery/v0/$blog->subdomain?path=")->assertUnprocessable()->assertSee('API Key not set');
    $this->get("/api/delivery/v0/$blog->subdomain?api_key=invalid&path=")->assertUnprocessable()->assertSee('API Key invalid');
});

it('calls the delivery API', function () {

    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));
    addRoute($blog, '/');
    addThemeTemplateFile($blog, 'just testing');

    $key = ApiKeysRepository::create($blog, 'test', ApiKeysTypeEnum::DELIVERY);

    $this->get("/api/delivery/v0/$blog->subdomain?api_key=$key->api_key&path=")
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('status', 200)
                ->where('content', base64_encode('just testing'))
                ->where('type', 'file')
                ->where('file_type', 'template')
                ->where('mime_type', 'text/html')
                ->where('cache', true)
                ->where('cache_control', 'no-cache, private')
                ->has('at')
        );
});

it('does not cache preview', function() {

    $blog = blogWithLanguageAndRoutes();
    $language = LanguageRepository::getPrimaryLanguage($blog);
    $post = addPublishedPost($blog);
    $id = PostPreviewSecretEncryptor::getPreviewSecret($post);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        '{{ _post.id }}{{ _lang.code }}',
    );

    $key = ApiKeysRepository::create($blog, 'test', ApiKeysTypeEnum::DELIVERY);
    $path = "/p/$id/$language->code";

    $this->get("/api/delivery/v0/$blog->subdomain?api_key=$key->api_key&path=$path")
        ->assertOk()
        ->assertJsonPath('type', 'file')
        ->assertJsonPath('cache', false)
        ->assertJsonPath('content', base64_encode($post->id.$language->code));

    $cache = (new CacheService())->blog($blog)->get($path);
    expect($cache)->toBeNull();

});
