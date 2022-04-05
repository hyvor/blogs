<?php
namespace Tests\Feature\DataAPI;

use App\Data\Objects\DataAPI\PostObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostTest extends TestCase
{

    use RefreshDatabase;

    private Blog $blog;
    private array $languages;
    private Post $post;
    private array $postObject;
    private array $postObject2;

    /**
     * Adds a post with 2 languages in it
     */
    protected function setUp() : void
    {

        parent::setUp();

        $this->blog = Blog::find(1);

        $post = Post::factory()
            ->has(
                PostVariant::factory()
                    ->count(2)
                    ->state(new Sequence(
                        ['language_id' => $this->blog->languages[0]],
                        ['language_id' => $this->blog->languages[1]],
                    ))
                    ->state(function() {
                        return ['status' => 'published'];
                    })
                    ,
                'variants'
            )
            ->create([
                'blog_id' => $this->blog,
            ]);

        // refetch with relationships
        $this->post = Post::find($post->id);

        $this->postObject = json_decode(
            json_encode(
                new PostObject($this->post, $this->blog, $this->blog->languages[0]
            )
        ), true);
        $this->postObject2 = json_decode(
            json_encode(
                new PostObject($this->post, $this->blog, $this->blog->languages[1]
            )
        ), true);

    }

    public function test_post_with_id()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id      
        ]);

        $response->assertStatus(200)->assertExactJson($this->postObject);

    }

    public function test_post_with_slug()
    {

        $response = $this->callDataApi('/post', [
            'slug' => $this->post->slug
        ]);

        $response->assertStatus(200)->assertExactJson($this->postObject);

    }

    public function test_post_with_id_and_lang()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => $this->blog->languages[1]->code
        ]);

        $response->assertStatus(200)->assertExactJson($this->postObject2);

    }

    public function test_post_with_invalid_language()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => 'jp'
        ]);

        $response->assertStatus(400);

    }

    public function test_post_missing()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id + 1,
        ]);

        $response->assertStatus(404);

    }

    public function test_post_with_invalid_variant()
    {

        // delete variant
        PostVariant::where('post_id', $this->post->id)
            ->where('language_id', $this->blog->languages[1]->id)
            ->delete();

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => $this->blog->languages[1]->code
        ]);

        $response->assertStatus(404);

    }

    public function test_cant_get_unpublished_posts()
    {

        PostVariant::where('post_id', $this->post->id)
            ->where('language_id', $this->blog->languages[0]->id)
            ->update(['status' => 'draft']);

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
        ]);

        $response->assertStatus(400);

    }

    public function test_wrong_blog_id_cant_access_posts()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
        ], Blog::find(2)->subdomain);

        $response->assertStatus(404); // post not found

    }

    // a basic keys filtering check
    // more thorough tests are done in KeyFilteringTest.php
    public function test_keys_filtering()
    {

        $response = $this->callDataApi('/post', [
            'id' => $this->post->id,
            'keys' => 'id,slug'
        ]);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('id')
                    ->has('slug')
                    ->missing('url');
            });

    }

}