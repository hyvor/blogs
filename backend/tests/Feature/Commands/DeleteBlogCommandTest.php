<?php

namespace Tests\Feature\Commands;

use App\Models\Blog;
use Tests\Case\DatabaseTestCase;

class DeleteBlogCommandTest extends DatabaseTestCase
{
    public function testDeleteBlogsByUserId(): void
    {
        $userId = 1;
        $blogs = Blog::factory()->count(2)->create(['user_id' => $userId]);
        $otherBlogs = Blog::factory()->count(2)->create();

        $this->artisan('delete:blog', ['--userId' => $userId])
            ->expectsConfirmation('Are you sure you want to delete all the blogs of the user with ID: ' . $userId . '?', 'yes')
            ->expectsConfirmation('Are you sure you want to delete blogs with following : ' . $blogs->pluck('subdomain')->implode(', ') . '?', 'yes')
            ->expectsOutput(now() . ' | Deleting blog: ' . $blogs[0]->subdomain)
            ->expectsOutput(now() . ' | Deleting blog: ' . $blogs[1]->subdomain)
            ->assertExitCode(0);

        $this->assertDatabaseMissing('blogs', ['id' => $blogs[0]->id]);
        $this->assertDatabaseMissing('blogs', ['id' => $blogs[1]->id]);
        $this->assertDatabaseHas('blogs', ['id' => $otherBlogs[0]->id]);
        $this->assertDatabaseHas('blogs', ['id' => $otherBlogs[1]->id]);
    }

    public function testDeleteABlog(): void
    {
        $userId = 1;
        $blogs = Blog::factory()->count(2)->create(['user_id' => $userId]);
        $otherBlogs = Blog::factory()->count(2)->create();

        $this->artisan('delete:blog', ['--blogId' => $blogs[0]->id])
            ->expectsConfirmation('Are you sure you want to delete blogs with following : ' . $blogs[0]->subdomain . '?', 'yes')
            ->expectsOutput(now() . ' | Deleting blog: ' . $blogs[0]->subdomain)
            ->assertExitCode(0);

        $this->assertDatabaseMissing('blogs', ['id' => $blogs[0]->id]);
        $this->assertDatabaseHas('blogs', ['id' => $blogs[1]->id]);
        $this->assertDatabaseHas('blogs', ['id' => $otherBlogs[0]->id]);
        $this->assertDatabaseHas('blogs', ['id' => $otherBlogs[1]->id]);
    }
}
