<?php

namespace App\Tests\Service\Post;

use App\Service\Post\FullTextSearchService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FullTextSearchService::class)]
class FullTextSearchServiceTest extends TestCase
{

    public function test_findClosestRegconfigByLanguageCode(): void
    {
        $fts = new FullTextSearchService();

        // english
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('EN'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en-US'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en-GB'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('eng'));

        // french
        $this->assertSame('french', $fts->findClosestRegconfigByLanguageCode('fr'));
        $this->assertSame('french', $fts->findClosestRegconfigByLanguageCode('fr-FR'));

        // unknown
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode('unknown'));
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode(''));
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode(null));
    }

    public function test_GetsSearchQuery(): void
    {
        $service = new FullTextSearchService();

        // english
        $this->assertSame('cake:*', $service->getSearchQuery('cake'));
        $this->assertSame('cake:* | pie:*', $service->getSearchQuery('cake pie'));
        $this->assertSame('cake:* | pie:* | ice:*', $service->getSearchQuery('cake pie ice'));

        // french
        $this->assertSame('éclair:*', $service->getSearchQuery('éclair'));
        $this->assertSame('éclair:* | tarte:*', $service->getSearchQuery('éclair tarte'));

        // bug with special chars
        $this->assertSame('what:* | is:* | a:* | cord:*', $service->getSearchQuery('what is a cord:'));
        $this->assertSame(
            'test:* | what:* | open?:*',
            $service->getSearchQuery('(test | what) : open?')
        );
    }

}
