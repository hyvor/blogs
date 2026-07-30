<?php

namespace App\Tests\Service\Integration\HyvorPost;

use App\Service\Delivery\PathMatcher;
use App\Service\Integration\HyvorPost\NewsletterCodeListener;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterCodeListener::class)]
class NewsletterCodeListenerTest extends KernelTestCase
{

    public function test_adds_code_when_hp_integration_enabled(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _newsletter }}');
        $hp = HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 394]);

        $pathMatcher = $this->getService(PathMatcher::class);
        $response = $pathMatcher->match($blog, '/');

        $this->assertStringContainsString(htmlspecialchars('hyvor-post-form newsletter-id="394"'), $response->content);
    }

    public function test_when_hp_integration_disabled(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _newsletter }}');
        $pathMatcher = $this->getService(PathMatcher::class);
        $response = $pathMatcher->match($blog, '/');

        $this->assertSame('', $response->content);
    }

}
