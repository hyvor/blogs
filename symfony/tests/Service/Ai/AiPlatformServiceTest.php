<?php

namespace App\Tests\Service\Ai;

use App\Entity\Blog;
use App\Service\Ai\AiPlatformService;
use App\Service\Ai\AiProvider;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\AI\Platform\Platform;

#[CoversClass(AiPlatformService::class)]
class AiPlatformServiceTest extends KernelTestCase
{

    public function test_fetches_for_platform(): void
    {
        $blog = new Blog();
        $service = $this->getService(AiPlatformService::class);

        foreach (AiProvider::cases() as $provider) {
            $blog->getMeta()->ai_provider = $provider;
            $platform = $service->getPlatformForBlog($blog);
            $this->assertInstanceOf(Platform::class, $platform);
        }
    }

}
