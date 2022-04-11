<?php

namespace Tests\Feature\DataAPI;

use App\Domains\Blog\FillNewBlog;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostsTest extends TestCase
{

    use RefreshDatabase;

    public function test_without_params()
    {
        $response = $this->callDataApi('/posts');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 25, fn (AssertableJson $json) =>
                        $json->where('language.code', 'en')
                            ->etc()
                    )
                    ->has('pagination');
            });
    }

    public function test_pages()
    {

        $response = $this->callDataApi('/posts', [
            'limit' => 2,
            'pages' => true
        ]);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data', 2, function (AssertableJson $json) {
                $json->where('is_page', true)
                    ->etc();
            })->etc();
        });

    }

    public function test_with_language()
    {
        $response = $this->callDataApi('/posts', [
            'language' => 'fr'
        ]);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 25, fn (AssertableJson $json) =>
                    $json->where('language.code', 'fr')
                        ->etc()
                )
                ->has('pagination');
            });
    }

    public function test_with_wrong_language()
    {
        $response = $this->callDataApi('/posts', [
            'language' => 'jp'
        ]);
        $response->assertStatus(400);
    }

    public function test_limit()
    {
        $response = $this->callDataApi('/posts', [
            'limit' => 3
        ]);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 3)
                    ->etc();
            });
    }

    // this should be run after test_limit()
    public function test_page()
    {

        $blog = Blog::find(1);
        Post::where('blog_id', 1)->delete(); // delete all seeded

        // now add 3
        $posts = Post::factory()->count(3)
            ->has(PostVariant::factory()
                ->count(1)
                ->state(['language_id' => $blog->languages[0], 'status' => 'published']), 
            'variants')
            ->create(['blog_id' => $blog]);

        $response = $this->callDataApi('/posts', [
            'limit' => 2,
            'page' => 2
        ], $blog->subdomain);
        
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($posts) {
                $json->has('data.0', function (AssertableJson $json) use ($posts) {
                    $json->where('id', $posts[2]->id)
                        ->etc();
                })
                ->etc();
            });

    }

    public function test_invalid_limit()
    {

        $this->callDataApi('/posts', [
            'limit' => 0,
        ])->assertStatus(400);

    }

    public function test_invalid_page()
    {

        $this->callDataApi('/posts', [
            'page' => -1,
        ])->assertStatus(400);

    }

    public function test_posts_invalid_sort() {
        $this->callDataApi('/posts', [
            'sort' => 'something_invalid'
        ])->assertStatus(400);
    }

    public function test_posts_invalid_sort_method()
    {
        $this->callDataApi('/posts', [
            'sort' => 'published_at SOME'
        ])->assertStatus(400);
    }

    public function test_posts_sort_published_at_desc() 
    {
        $response = $this->callDataApi('/posts', [
            'sort' => 'published_at',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] >= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] >= $response['data'][2]['published_at']
        );
    }

    public function test_posts_sort_published_at_asc() 
    {
        $response = $this->callDataApi('/posts', [
            'sort' => 'published_at ASC',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] <= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] <= $response['data'][2]['published_at']
        );
    }

    public function test_posts_sort_by_created_at()
    {

        $response = $this->callDataApi('/posts', [
            'sort' => 'created_at',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
            $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
        );

    }

    public function test_posts_sort_by_updated_at_asc()
    {

        $response = $this->callDataApi('/posts', [
            'sort' => 'updated_at ASC',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['updated_at'] <= $response['data'][1]['updated_at'] &&
            $response['data'][1]['updated_at'] <= $response['data'][2]['updated_at']
        );
    }


    public function test_posts_sort_by_is_featured()
    {
        // assumption : Posts added in the Seeder are not featured
        $post = $this->getAPost();
        $post->is_featured = true;
        $post->save();

        $response = $this->callDataApi('/posts', [
            'sort' => 'is_featured DESC',
            'limit' => 3
        ])->json();

        $this->assertTrue($response['data'][0]['is_featured'] && $response['data'][0]['id'] === $post->id);
    }

    public function test_posts_sort_by_title()
    {

        $response = $this->callDataApi('/posts', [
            'sort' => 'title DESC',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['title'] >= $response['data'][1]['title'] &&
            $response['data'][1]['title'] >= $response['data'][2]['title']
        );

    }

    public function test_posts_sort_by_words()
    {

        $response = $this->callDataApi('/posts', [
            'sort' => 'words DESC',
            'limit' => 3
        ])->json();
    
        $this->assertTrue(
            $response['data'][0]['words'] >= $response['data'][1]['words'] &&
            $response['data'][1]['words'] >= $response['data'][2]['words']
        );
        
    }

    public function test_posts_keys()
    {
        $response = $this->callDataApi('/posts', [
            'keys' => 'id',
            'limit' => 3
        ]);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data', 3, function (AssertableJson $json) {
                $json->has('id')
                    ->missing('slug');
            })->etc();
        });
    }

    public function test_posts_filter_by_id() {
        $post = $this->getAPost();
        
        $response = $this->callDataApi('/posts', [
            'filter' => "id=$post->id"
        ]);
        $response
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonCount(1, 'data');
    }

    public function test_posts_filter_by_slug()
    {
        $post = $this->getAPost();
        $post->update(['slug' => 'some-new-slug']);
        
        $response = $this->callDataApi('/posts', [
            'filter' => "slug=$post->slug"
        ]);
        $response
            ->assertJsonPath('data.0.slug', $post->slug)
            ->assertJsonCount(1, 'data');
    }

    public function test_posts_filter_by_is_featured()
    {

        $post = $this->getAPost();
        $post->update(['is_featured' => true]);
        
        $response = $this->callDataApi('/posts', [
            'filter' => "is_featured=true"
        ]);
        $response
            ->assertJsonPath('data.0.slug', $post->slug)
            ->assertJsonCount(1, 'data');

    }

    /**
     * @group new
     */
    public function test_posts_filter_by_published_at()
    {

        $time = new Carbon('yesterday');

        $post = $this->getAPost();
        $post->update(['published_at' => $time]);
        
        $response = $this->callDataApi('/posts', [
            'filter' => "published_at=yesterday"
        ]);
        $response
            ->assertJsonPath('data.0.published_at', $time->timestamp)
            ->assertJsonCount(1, 'data');

    }

    private function getAPost()
    {
        $post = Post::where('blog_id', config('test.blog_id'))->where('is_page', false)->first();
        // make sure to publish the variants
        $post->variants->map(fn ($v) => $v->update(['status' => 'published']));
        return $post;
    }
    
}
