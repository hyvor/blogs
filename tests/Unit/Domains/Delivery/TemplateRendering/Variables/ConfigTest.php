<?php

namespace Tests\Unit\Domains\Delivery\Rendering;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _config variable', function () {
    $name = 'Hyvor';
    $nestedValue = 'Blogs';

    $configYaml = <<<YAML
    name: $name
    nested:
        value: $nestedValue
    YAML;

    $content = <<<TWIG
    {{ _config.name }}
    {{ _config.nested.value }}
    TWIG;

    $rendered = "$name\n$nestedValue";

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        null,
        'config.yaml',
        $configYaml,
    );

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->content)->toBe($rendered);
});

it('returns 500 and error message when config.yaml is wrong', function () {
    $configYaml = '@invalid:yaml:is:here';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        null,
        'config.yaml',
        $configYaml,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->status)->toBe(500);
    expect($responseObject->content)->toContain('Unable to parse config.yam');
});
