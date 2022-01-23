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

        Blog::factory()

        $faker = \Faker\Factory::create();

        $blogs = [['supun', "Supun's Blog"], ['ishini', "Ishini's Blog"]];

        foreach ($blogs as $blogData) {
            $blog = Blog::create([
                'user_id' => 1,
                'subdomain' => $blogData[0],
                'name' => $blogData[1],
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
                $prosemirrorJson = [
                    'type' => 'doc',
                    'content' => []
                ];
                foreach ($paragraphs as $para) {
                    $prosemirrorJson['content'][] = [
                        'type' => 'paragraph',
                        'content' => [[
                            'type' => 'text',
                            'text' => $para
                        ]]
                    ];
                }

                $status = ['draft', 'published', 'deleted', 'scheduled'];
                $post = Post::create([
                    'blog_id' => $blog->id,
                    'content' => json_encode($prosemirrorJson),
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'description' => $faker->sentence,
                    'status' => $status[ array_rand($status) ],

                    'reading_time' => 2,
                ]);

                PostTag::create([
                    'post_id' => $post->id,
                    'tag_id' => $tags[ array_rand($tags) ]->id
                ]);
            }
        }
    }
}
