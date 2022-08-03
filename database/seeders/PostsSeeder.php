<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Seeder;

class PostsSeeder extends Seeder
{
    public function __construct(private int $count)
    {
    }

    public function run()
    {
        Post::factory()
            ->count($this->count)
            ->has(PostVariant::factory()->create())
            ->create([
                'blog_id' => config('test.blog_id'),
            ]);
    }
}
