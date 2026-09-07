<?php

namespace App\Tests\Service\LinkAnalysis;

use App\Service\LinkAnalysis\StatusCheck\KnownFirewall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(KnownFirewall::class)]
class KnownFirewallTest extends TestCase
{
    public function test_detect(): void
    {
        $this->assertNull(KnownFirewall::detect([]));
        $this->assertNull(KnownFirewall::detect(['cf-mitigated' => '']));
        $this->assertSame(
            KnownFirewall::CLOUDFLARE_CHALLENGE,
            KnownFirewall::detect(['cf-mitigated' => 'challenge'])
        );
    }
}
