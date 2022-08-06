<?php

namespace Tests\Feature\DeliveryApi;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Testing\Fluent\AssertableJson;

it('calls the delivery API', function() {

    ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'just testing'
    );

    $this->get('/api/delivery/v0/test?path=')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', 200)
                ->where('content', base64_encode('just testing'))
                ->where('type', 'file')
                ->where('file_type', 'template')
                ->where('mime_type', 'text/html')
                ->where('cache', true)
                ->has('at')
        );

});
