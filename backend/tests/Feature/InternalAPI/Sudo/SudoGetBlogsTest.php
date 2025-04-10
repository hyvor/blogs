<?php

namespace Tests\Feature\InternalAPI\Sudo;

use App\Models\Blog;
use Hyvor\Internal\InternalApi\Testing\CallsInternalApi;
use Tests\Case\DatabaseTestCase;

class SudoGetBlogsTest extends DatabaseTestCase
{

    use CallsInternalApi;

    public function testGetsBlogs(): void
    {
        Blog::factory()->count(3)->create();

        $this->internalApi(
            'GET',
            '/core/sudo/blogs',
        )
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function testFiltersBlogById(): void
    {
        $blogs = Blog::factory()->count(3)->create();

        $this->internalApi(
            'GET',
            '/core/sudo/blogs',
            ['blog_id' => $blogs[1]?->id]
        )
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $blogs[1]?->id);
    }

    public function testFiltersBlogBySubdomain(): void
    {
        $blog1 = Blog::factory()->create([
            'id' => 2001,
            'created_at' => now()->subDays(31),
            'trial_ends_at' => now()->subDays(10),
            'subdomain' => 'sub1'
        ]);

        $this->internalApi(
            'GET',
            '/core/sudo/blogs',
            ['subdomain' => $blog1->subdomain]
        )
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.subdomain', $blog1->subdomain);
    }
    
    public function testFiltersBlogsByUserId(): void
    {
        $blog1 = Blog::factory()->create([
            'id' => 2001,
            'created_at' => now()->subDays(31),
            'trial_ends_at' => now()->subDays(10),
            'hyvor_user_id' => 1001
        ]);

        $this->internalApi(
            'GET',
            '/core/sudo/blogs',
            ['user_id' => $blog1->hyvor_user_id]
        )
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.hyvor_user_id', $blog1->hyvor_user_id);
    }

}