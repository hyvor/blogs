<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\PostsController;
use App\Api\Data\DataApiHelper;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[CoversClass(DataApiHelper::class)]
class HelperTest extends ApiTestCase
{
    private DataApiHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var DataApiHelper $helper */
        $helper = $this->getContainer()->get(DataApiHelper::class);
        $this->helper = $helper;
    }

    public function test_returns_correct_language(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $en = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        $fr = LanguageFactory::createOne(['blog' => $blog, 'code' => 'fr', 'is_primary' => false]);

        $this->assertSame($en->getCode(), $this->helper->getLanguage($blog, 'en')->getCode());
        $this->assertSame($fr->getCode(), $this->helper->getLanguage($blog, 'fr')->getCode());
        $this->assertSame($en->getCode(), $this->helper->getLanguage($blog, null)->getCode());
    }

    public function test_throws_error_if_language_not_found(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->helper->getLanguage($blog, 'jp');
    }

    public function test_sort(): void
    {
        $orderBys = $this->helper->getSort('published_at', PostsController::ALLOWED_SORTS);

        $this->assertSame(
            [[PostsController::ALLOWED_SORTS['published_at'], 'DESC']],
            $orderBys
        );
    }

    public function test_sort_with_null(): void
    {
        $orderBys = $this->helper->getSort(null, PostsController::ALLOWED_SORTS);

        $this->assertSame(
            [[PostsController::ALLOWED_SORTS['published_at'], 'DESC']],
            $orderBys
        );
    }

    public function test_multi_sort(): void
    {
        $orderBys = $this->helper->getSort('published_at DESC,id ASC', PostsController::ALLOWED_SORTS);

        $this->assertSame(
            [
                [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [PostsController::ALLOWED_SORTS['id'], 'ASC'],
            ],
            $orderBys
        );
    }

    public function test_multi_sort_with_space(): void
    {
        $orderBys = $this->helper->getSort('published_at DESC, id ASC', PostsController::ALLOWED_SORTS);

        $this->assertSame(
            [
                [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [PostsController::ALLOWED_SORTS['id'], 'ASC'],
            ],
            $orderBys
        );
    }

    public function test_multi_sort_with_additional_spaces(): void
    {
        $orderBys = $this->helper->getSort('published_at  DESC, id  ASC ', PostsController::ALLOWED_SORTS);

        $this->assertSame(
            [
                [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [PostsController::ALLOWED_SORTS['id'], 'ASC'],
            ],
            $orderBys
        );
    }

    public function test_limit(): void
    {
        $this->assertSame(20, $this->helper->getLimit(20));
        $this->assertSame(DataApiHelper::DEFAULT_LIMIT, $this->helper->getLimit(null));
        $this->assertSame(DataApiHelper::MAX_LIMIT, $this->helper->getLimit(DataApiHelper::MAX_LIMIT + 100));
    }

    public function test_page(): void
    {
        $this->assertSame(DataApiHelper::DEFAULT_PAGE, $this->helper->getPage(null));
    }

    public function test_offset(): void
    {
        $this->assertSame(20, $this->helper->getOffset(2, 20));
    }
}
