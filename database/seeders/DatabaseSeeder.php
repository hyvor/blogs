<?php

namespace Database\Seeders;

use App\Domains\Language\LanguageRepository;
use App\Domains\Redirect\RedirectRepository;
use App\Domains\Route\RouteRepository;
use App\Models\Blog;
use App\Models\Media;
use App\Models\BlogThemeFile;
use App\Models\Post;
use App\Models\PostAuthor;
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

        $blogs = [['test', "Test Blog", 'hyvorblogscustom.test'], ['test2', "Test2 Blog"]];

        foreach ($blogs as $blogData) {
            $blog = Blog::create([
                'user_id' => 1,
                'subdomain' => $blogData[0],
                'name' => $blogData[1],
                'hosting_domain' => $blogData[2] ?? null
            ]);

            $blog->createAsCustomer([
                'trial_ends_at' => now()->addDays(30)
            ]);

            RouteRepository::addDefaultRoutes($blog);
            $language = LanguageRepository::addDefaultLanguage($blog);
            LanguageRepository::createLanguage($blog->id, 'fr', 'French');

            RedirectRepository::createRedirect($blog->id, '/redirects', 'https://example.com');

            User::create([
                'blog_id' => $blog->id,
                'user_id' => $blog->user_id,
                'role' => 'owner',
                'status' => 'active',
                'slug' => "test",
                'name' => 'Test User',
                'email' => 'test@hyvor.com'
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
            foreach (range(0, 200) as $i) {
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
                $status = $i === 0 ? 'published' : $status[ array_rand($status) ];
                $post = Post::create([
                    'blog_id' => $blog->id,
                    'language_id' => $language->id,
                    'content' => json_encode($prosemirrorJson),
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'published_at' => $status === 'published' ? $faker->dateTime() : null,
                    'description' => $faker->sentence,
                    'status' => $status,

                    'is_page' => (bool) rand(0,1),

                    'reading_time' => 2,
                ]);

                PostTag::create([
                    'post_id' => $post->id,
                    'tag_id' => $tags[ array_rand($tags) ]->id
                ]);

                PostAuthor::create([
                    'post_id' => $post->id,
                    'user_id' => 1
                ]);
            }

            foreach (range(0, 15) as $i) {
                /* Media::create([
                    'blog_id' => $blog->id,
                    'url' => 'https://picsum.photos/' . rand(200, 500) . '/' . rand(200, 500),
                    'size' => rand(1000000, 9000000),
                    'name' => $faker->name,
                    'extension' => 'jpg'
                ]); */
            }
        
        }


        $this->call([
            BlogThemeFilesSeeder::class
        ]);

    }
}
