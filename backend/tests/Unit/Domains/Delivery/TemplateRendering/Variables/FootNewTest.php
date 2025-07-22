<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Hook\BlogCustomCodeHookEvent;
use App\Domains\Theme\ThemeFilesRepository;
use Database\Factories\BlogFactory;
use Illuminate\Support\Facades\Event;
use Tests\Case\AppTestCase;

class FootNewTest extends AppTestCase
{

    public function test_hooks_custom_code(): void
    {
        Event::listen(BlogCustomCodeHookEvent::class, function (BlogCustomCodeHookEvent $event) {
            $event->appendCodeFoot('<!-- Custom Foot Code -->');
        });

        $blog = BlogFactory::withLanguageAndRoutes();
        $content = '{{ _foot | template }}';

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::TEMPLATES,
            'index.twig',
            $content,
        );

        $pathMatcher = new PathMatcher($blog, '/');
        $responseObject = $pathMatcher->getResponseObject();

        $content = $responseObject->content;
        $this->assertStringContainsString('<!-- Custom Foot Code -->', $content);

        Event::forget(BlogCustomCodeHookEvent::class);
    }

}