<?php
namespace Tests\Feature\DataAPI;

use App\Domains\Post\PostSearchRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSearchTest extends TestCase
{

    use RefreshDatabase;

    public function test_post_search()
    {

        $posts = PostSearchRepository::search('hi', 1, 1, false);

        dd($posts);

    }

}