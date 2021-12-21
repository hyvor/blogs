<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $faker = \Faker\Factory::create();

        $blog = Blog::create([
            'user_id' => 1,
            'subdomain' => 'supun',
            'name' => "Supun's Blog",
        ]);

        User::create([
            'blog_id' => $blog->id,
            'user_id' => $blog->user_id,
            'role' => 'owner',
            'status' => 'active',
            'slug' => "supun",
            'name' => 'Supun Kavinda',
            'email' => 'supun@hyvor.com'
        ]);

        $tags = [];
        foreach (range(0, 9) as $i) {
            $name = $faker->name();
            $tags[] = Tag::create([
                'blog_id' => $blog->id,
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }

        $posts = [];
        foreach (range(0, 100) as $i) {

            $title = $faker->sentence;

            $paragraphs = $faker->paragraphs(rand(2, 6));
            $content = "";
            foreach ($paragraphs as $para) {
                $content .= "<p>{$para}</p>";
            }


            $post = Post::create([
                'blog_id' => $blog->id,
                'content' => $content,
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $faker->sentence,

                'reading_time' => 2,
            ]);

            PostTag::create([
                'post_id' => $post->id,
                'tag_id' => $tags[ array_rand($tags) ]->id
            ]);
        }

    }
}
