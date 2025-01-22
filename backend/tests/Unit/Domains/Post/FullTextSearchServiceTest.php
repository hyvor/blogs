<?php

namespace Tests\Unit\Domains\Post;

use App\Domains\Post\FullTextSearchService;
use Tests\BaseTestCase;

class FullTextSearchServiceTest extends BaseTestCase
{

    public function testGetsRegConfig(): void
    {

        $fts = new FullTextSearchService();

        // english
        $this->assertEquals('english', $fts->findClosestRegconfigByLanguageCode('en'));
        $this->assertEquals('english', $fts->findClosestRegconfigByLanguageCode('EN'));
        $this->assertEquals('english', $fts->findClosestRegconfigByLanguageCode('en-US'));
        $this->assertEquals('english', $fts->findClosestRegconfigByLanguageCode('en-GB'));
        $this->assertEquals('english', $fts->findClosestRegconfigByLanguageCode('eng'));

        // fr
        $this->assertEquals('french', $fts->findClosestRegconfigByLanguageCode('fr'));
        $this->assertEquals('french', $fts->findClosestRegconfigByLanguageCode('fr-FR'));

        // unknown
        $this->assertEquals('simple', $fts->findClosestRegconfigByLanguageCode('unknown'));
        $this->assertEquals('simple', $fts->findClosestRegconfigByLanguageCode(''));
        $this->assertEquals('simple', $fts->findClosestRegconfigByLanguageCode(null));

    }

}
