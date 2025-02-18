<?php
declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer\LinkStatusCheck\Ignore;

use App\Domains\LinkAnalyzer\LinkStatusCheck\Ignore\KnownFirewall;
use App\Domains\LinkAnalyzer\LinkStatusCheck\Ignore\KnownFirewallEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Case\AppTestCase;

#[CoversClass(KnownFirewall::class)]
class KnownFirewallTest extends AppTestCase
{

    public function testIsKnownFirewall(): void
    {
        $this->assertNull(KnownFirewall::isKnownFirewall([]));
        $this->assertNull(KnownFirewall::isKnownFirewall(['cf-mitigated' => '']));
        $this->assertSame(
            KnownFirewallEnum::CLOUDFLARE_CHALLENGE,
            KnownFirewall::isKnownFirewall(['cf-mitigated' => 'challenge'])
        );
    }

}