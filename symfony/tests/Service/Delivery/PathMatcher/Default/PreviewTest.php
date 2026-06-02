<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\PreviewProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(PreviewProcessor::class)]
class PreviewTest extends KernelTestCase
{
    public function test_preview_is_not_yet_implemented(): void
    {
        // TODO: implement once TemplateRenderer is migrated (Part 3+)
        $this->markTestIncomplete('PreviewProcessor requires TemplateRenderer (not yet migrated)');
    }
}
