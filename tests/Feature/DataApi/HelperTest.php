<?php

use App\Exceptions\TrustedException;
use App\Http\Controllers\DataApi\Helper;
use App\Http\Controllers\DataApi\PostsController;
use App\Models\Blog;

it('returns the correct language', function() {
   
    $blog = Blog::find(config('test.blog_id'));
    
    $en = $blog->languages[0];
    $fr = $blog->languages[1];
    
    $this->assertEquals($en, Helper::getLanguage($blog, 'en'));
    $this->assertEquals($en, Helper::getLanguage($blog, null));
    $this->assertEquals($fr, Helper::getLanguage($blog, 'fr'));
    
});

it('throws an error if language is not found', function() {
    
    $blog = Blog::find(config('test.blog_id'));
    
    $this->expectException(TrustedException::class);
    
    Helper::getLanguage($blog, 'jp');
    
});

it('tests sort', function() {

    $orderBys = Helper::getSort('published_at', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC']
        ],
        $orderBys
    );
    
});

it('tests sort with null', function() {

    $orderBys = Helper::getSort(null, PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC']
        ],
        $orderBys
    );
    
});

it('tests multi sort', function() {

    $orderBys = Helper::getSort('published_at DESC,id ASC', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC']
        ],
        $orderBys
    );
    
});

it('tests multi sort with space', function() {

    $orderBys = Helper::getSort('published_at DESC, id ASC', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC']
        ],
        $orderBys
    );
    
});

it('tests multi sort with additional spaces', function() {
    
    $orderBys = Helper::getSort('published_at  DESC, id  ASC ', PostsController::ALLOWED_SORTS);

    $this->assertEquals(
        [
            [PostsController::ALLOWED_SORTS['published_at'], 'DESC'],
            [PostsController::ALLOWED_SORTS['id'], 'ASC']
        ],
        $orderBys
    );
    
});

it('tests limit', function() {

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

it('tests page', function() {
   
    $this->assertEquals(
        Helper::getPage(null),
        Helper::DEFAULT_PAGE
    );
    
});

it('tests offset', function() {
   
    $this->assertEquals(
        Helper::getOffset(2, 20),
        20
    );
    
});
