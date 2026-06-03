<?php

namespace App\Tests\Service\Delivery\PathMatcher;

use App\Entity\Enum\RedirectType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Redirect\RedirectService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(RedirectService::class)]
class RedirectTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_static_redirect(): void
    {
        $blog = BlogFactory::createOne();
        RedirectFactory::createOne([
            'blog' => $blog,
            'dynamic' => false,
            'path' => '/redirect',
            'to' => 'https://somewhere.com',
            'type' => RedirectType::PERMANENT,
        ]);

        $response = $this->pathMatcher()->match($blog, '/redirect');

        $this->assertSame(DeliveryResponseType::REDIRECT, $response->type);
        $this->assertSame('https://somewhere.com', $response->to);
        $this->assertSame(301, $response->status);
    }

    public function test_matches_dynamic_redirect(): void
    {
        $blog = BlogFactory::createOne();
        RedirectFactory::createOne([
            'blog' => $blog,
            'dynamic' => true,
            'path' => '/redirect/(.*)',
            'to' => 'https://somewhere.com/$1',
            'type' => RedirectType::TEMPORARY,
        ]);

        $response = $this->pathMatcher()->match($blog, '/redirect/123/456');

        $this->assertSame(DeliveryResponseType::REDIRECT, $response->type);
        $this->assertSame('https://somewhere.com/123/456', $response->to);
        $this->assertSame(302, $response->status);
    }

    public function test_does_not_match_if_redirect_not_found(): void
    {
        $blog = BlogFactory::createOne();

        $response = $this->pathMatcher()->match($blog, '/redirect/path');

        $this->assertSame(404, $response->status);
    }
}
