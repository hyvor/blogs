<?php

namespace Tests\Unit\Domains\Post;

use App\Domains\Post\FullTextSearchService;
use Tests\Case\AppTestCase;

class FullTextSearchServiceTest extends AppTestCase
{

    public function testGetsRegConfig(): void
    {
        $fts = new FullTextSearchService();

        // english
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('EN'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en-US'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('en-GB'));
        $this->assertSame('english', $fts->findClosestRegconfigByLanguageCode('eng'));

        // fr
        $this->assertSame('french', $fts->findClosestRegconfigByLanguageCode('fr'));
        $this->assertSame('french', $fts->findClosestRegconfigByLanguageCode('fr-FR'));

        // unknown
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode('unknown'));
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode(''));
        $this->assertSame('simple', $fts->findClosestRegconfigByLanguageCode(null));
    }

    public function testGetsSearchQuery(): void
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
