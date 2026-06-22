<?php

namespace App\Tests\Service\Delivery\PathMatcher;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class NotFoundTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_returns_404(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);

        $response = $this->pathMatcher()->match($blog, '/not-found');

        $this->assertSame(404, $response->status);
        $this->assertSame('404', $response->content);
    }

    public function test_returns_404_twig_rendered(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => '404.twig',
            'content' => '404 not found. {{ _blog.subdomain }}',
        ]);

        $response = $this->pathMatcher()->match($blog, '/not-found');

        $this->assertSame(404, $response->status);
        $this->assertSame('404 not found. ' . $blog->getSubdomain(), $response->content);
    }
}
