<?php

use App\Exceptions\TrustedException;
use App\Http\Controllers\DataApi\Helper;
use App\Http\Controllers\DataApi\PostsController;
use App\Models\Blog;

it('returns the correct language', function () {
    $blog = blog();

    $en = addPrimaryLanguage($blog);
    $fr = addLanguage($blog);

    expect(Helper::getLanguage($blog, $en->code)->code)->toBe($en->code);
    expect(Helper::getLanguage($blog, $fr->code)->code)->toBe($fr->code);
    expect(Helper::getLanguage($blog, null)->code)->toBe($en->code);
});

it('throws an error if language is not found', function () {
    $blog = blog();

    $this->expectException(TrustedException::class);

    Helper::getLanguage($blog, 'jp');
});

it('tests sort', function () {
    $orderBys = Helper::getSort('published_at', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
        ],
        $orderBys
    );
});

it('tests sort with null', function () {
    $orderBys = Helper::getSort(null, PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
        ],
        $orderBys
    );
});

it('tests multi sort', function () {
    $orderBys = Helper::getSort('published_at DESC,id ASC', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC'],
        ],
        $orderBys
    );
});

it('tests multi sort with space', function () {
    $orderBys = Helper::getSort('published_at DESC, id ASC', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC'],
        ],
        $orderBys
    );
});

it('tests multi sort with additional spaces', function () {
    $orderBys = Helper::getSort('published_at  DESC, id  ASC ', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC'],
        ],
        $orderBys
    );
});

it('tests limit', function () {
    $this->assertEquals(
        Helper::getLimit(20),
        20
    );
    $this->assertEquals(
        Helper::getLimit(null),
        Helper::DEFAULT_LIMIT
    );
    $this->assertEquals(
        Helper::getLimit(Helper::MAX_LIMIT + 100),
        Helper::MAX_LIMIT
    );
});

it('tests page', function () {
    $this->assertEquals(
        Helper::getPage(null),
        Helper::DEFAULT_PAGE
    );
});

it('tests offset', function () {
    $this->assertEquals(
        Helper::getOffset(2, 20),
        20
    );
});
