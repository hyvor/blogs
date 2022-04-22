<?php

use App\Http\Controllers\DataAPI\DataAPIHelper;
use App\Http\Controllers\DataAPI\DataAPIPostsController;
use Tests\TestCase;

class HelperTest extends TestCase
{


    public function test_helper_sort()
    {
        $orderBys = DataAPIHelper::getSort('published_at', DataAPIPostsController::ALLOWED_SORTS);

        $this->assertEquals(
            [
                [DataAPIPostsController::ALLOWED_SORTS['published_at'], 'DESC']
            ],
            $orderBys
        );
    }

    public function test_helper_sort_null()
    {
        $orderBys = DataAPIHelper::getSort(null, DataAPIPostsController::ALLOWED_SORTS);

        $this->assertEquals(
            [
                [DataAPIPostsController::ALLOWED_SORTS['published_at'], 'DESC']
            ],
            $orderBys
        );
    }

    public function test_helper_sort_multi()
    {

        $orderBys = DataAPIHelper::getSort('published_at DESC,id ASC', DataAPIPostsController::ALLOWED_SORTS);

        $this->assertEquals(
            [
                [DataAPIPostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [DataAPIPostsController::ALLOWED_SORTS['id'], 'ASC']
            ],
            $orderBys
        );

    }

    public function test_helper_sort_multi_with_space()
    {

        $orderBys = DataAPIHelper::getSort('published_at DESC, id ASC', DataAPIPostsController::ALLOWED_SORTS);

        $this->assertEquals(
            [
                [DataAPIPostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [DataAPIPostsController::ALLOWED_SORTS['id'], 'ASC']
            ],
            $orderBys
        );

    }

    public function test_helper_sort_multi_with_additional_spaces()
    {

        $orderBys = DataAPIHelper::getSort('published_at  DESC, id  ASC ', DataAPIPostsController::ALLOWED_SORTS);

        $this->assertEquals(
            [
                [DataAPIPostsController::ALLOWED_SORTS['published_at'], 'DESC'],
                [DataAPIPostsController::ALLOWED_SORTS['id'], 'ASC']
            ],
            $orderBys
        );

    }

    public function test_limit() {
        $this->assertEquals(
            DataAPIHelper::getLimit(25),
            25
        );
        $this->assertEquals(
            DataAPIHelper::getLimit(null),
            25
        );
        $this->assertEquals(
            DataAPIHelper::getLimit(1000),
            250
        );
    }

    

}