<?php

namespace Database\Seeders;

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Route;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;
use Faker\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
        $faker = Factory::create();
        $fakerFr = Factory::create('fr_FR');

        $blogs = Blog::factory()
            ->count(4)
            ->state(new Sequence(
                [
                    'subdomain' => 'test',
                ],
                [
                    'subdomain' => 'custom',
                    'hosting_at' => 'self',
                    'hosting_domain' => 'hyvorblogscustom.test',
                ],
                [
                    'subdomain' => 'self',
                    'hosting_at' => 'self',
                    'hosting_url' => 'https://blogs.hyvor.test/blog',
                ],
                [
                    'subdomain' => 'dev',
                    'type' => 'dev',
                    'hosting_at' => 'self',
                    'hosting_url' => 'http://127.0.0.1:8885',
                ]
            ))
            ->create();

        // Add additional 20 blogs
        /*$blogs->push(
            ...Blog::factory()->count(20)->create()
        );*/

        foreach ($blogs as $blog) {
            /*$blog->createAsCustomer([
                'trial_ends_at' => now()->addDays(config('limits.trial_days')),
            ]);*/

            $english = Language::factory()->create([
                'blog_id' => $blog,
                'code' => 'en',
                'name' => 'English',
                'is_primary' => true,
            ]);
            $french = Language::factory()->create([
                'blog_id' => $blog,
                'code' => 'fr',
                'name' => 'French',
                'is_primary' => false,
            ]);

            BlogVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $english],
                    ['language_id' => $french]
                ))
                ->create([
                    'blog_id' => $blog,
                ]);

            // tags
            $tags = Tag::factory()
                ->count(10)
                ->has(
                    TagVariant::factory()
                        ->count(2)
                        ->state(new Sequence(
                            ['language_id' => $english],
                            ['language_id' => $french]
                        )),
                    'variants'
                )
                ->create([
                    'blog_id' => $blog,
                ]);

            // users
            $users = User::factory()
                ->count(2)
                ->has(
                    UserVariant::factory()
                        ->count(2)
                        ->state(new Sequence(
                            ['language_id' => $english],
                            ['language_id' => $french]
                        )),
                    'variants'
                )
                ->state(new Sequence(
                    ['role' => 'owner', 'hyvor_user_id' => config('test.hyvor_user_id')],
                    ['role' => 'admin', 'hyvor_user_id' => 2]
                ))
                ->create([
                    'blog_id' => $blog,
                    'status' => 'active',
                ]);

            // posts
            /**
             * posts and pages 10 each
             * about 3 draft, 3 published, 3 scheduled
             */
            $posts = Post::factory()
                ->count(10)
                ->has(
                    PostVariant::factory()
                        ->count(2)
                        ->state(new Sequence(
                            ['language_id' => $english],
                            ['language_id' => $french]
                        ))
                        ->state(new Sequence(
                            ['status' => 'draft'],
                            ['status' => 'published'],
                            ['status' => 'scheduled']
                        )),
                    'variants'
                )
                ->state(new Sequence(
                    ['is_page' => true],
                    ['is_page' => false]
                ))
                ->create([
                    'blog_id' => $blog,
                ]);

            // connect posts and tags
            $posts->map(function ($post) use ($tags, $users) {
                $tags->random(3)->map(fn ($tag) => PostTag::create([
                    'post_id' => $post->id,
                    'tag_id' => $tag->id,
                ]));

                $users->map(fn ($user) => PostAuthor::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]));
            });

            /**
             * Other fillers are mimicked inside this seeder
             * However, we'll here just use the Route filler as the code will be the same for testing
             */
            foreach (RouteFiller::ROUTES as $route) {
                $blog->routes()->create($route);
            }
        }

        /*$this->call([
            BlogThemeFilesSeeder::class
        ]);*/
    }
}
