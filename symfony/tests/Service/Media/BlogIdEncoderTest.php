<?php

namespace App\Tests\Service\Media;

use App\Service\Media\BlogIdEncoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlogIdEncoder::class)]
class BlogIdEncoderTest extends TestCase
{

    #[TestWith([1, 'AQ'])]
    #[TestWith([255, '_w'])]
    #[TestWith([256, 'AQA'])]
    #[TestWith([1234, 'BNI'])]
    #[TestWith([65535, '__8'])]
    #[TestWith([16777216, 'AQAAAA'])]
    #[TestWith([PHP_INT_MAX, 'f_________8'])]
    public function test_encodes_and_decodes(int $id, string $encoded): void
    {
        $this->assertSame($encoded, BlogIdEncoder::encode($id));
        $this->assertSame($id, BlogIdEncoder::decode($encoded));
    }

    public function test_round_trips(): void
    {
        foreach ([...range(1, 2000), 99999, 1000000, 2 ** 31, 2 ** 40 + 7] as $id) {
            $encoded = BlogIdEncoder::encode($id);
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $encoded);
            $this->assertSame($id, BlogIdEncoder::decode($encoded));
        }
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    public function test_encode_rejects_non_positive_ids(int $id): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BlogIdEncoder::encode($id);
    }

    #[TestWith([''])]
    #[TestWith(['A'])] // too short
    #[TestWith(['AA'])] // zero
    #[TestWith(['AAE'])] // leading zero byte (non-canonical 1)
    #[TestWith(['AR'])] // non-zero unused trailing bits (non-canonical 1)
    #[TestWith(['AQ=='])] // padding
    #[TestWith(['A+'])] // standard base64, not base64url
    #[TestWith(['A/'])]
    #[TestWith(['A.'])]
    #[TestWith(['gAAAAAAAAAA'])] // overflows to a negative int
    #[TestWith(['AQAAAAAAAAAA'])] // more than 8 bytes
    public function test_decode_rejects_invalid_and_non_canonical_values(string $encoded): void
    {
        $this->assertNull(BlogIdEncoder::decode($encoded));
    }

}
