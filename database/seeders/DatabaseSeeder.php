<?php

namespace Database\Seeders;

use App\Domains\Post\PostSearchRepository;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;
use App\Models\Navigation;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostVariant;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\TagVariant;
use Faker\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
                    'hosting_domain' => 'hyvorblogscustom.test'
                ],
                [
                    'subdomain' => 'self',
                    'hosting_at' => 'self',
                    'hosting_url' => 'https://blogs.hyvor.test/blog'
                ],
                [
                    'subdomain' => 'dev',
                    'type' => 'dev',
                    'hosting_at' => 'self',
                    'hosting_url' => 'http://127.0.0.1:8885'
                ]
            ))
            ->create();

        /*$blogs->push(
            ...Blog::factory()->count(20)->create()
        );*/
        
        foreach ($blogs as $blog) {
            
            $english = $blog->languages[0];
            $french = Language::factory()->create([
                'blog_id' => $blog,
                'code' => 'fr',
                'name' => "French" 
            ]);
            
            BlogVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $english],
                    ['language_id' => $french]
                ))
                ->create([
                    'blog_id' => $blog
                ]);
            
            // tags
            $tags = Tag::factory()
                ->count(10)
                ->has(
                    TagVariant::factory()
                        ->count(2)
                        ->state(new Sequence(
                            [
                                'language_id' => $english
                            ],
                            [
                                'language_id' => $french
                            ]
                        ))
                    , 
                    'variants'
                )
                ->create([
                    'blog_id' => $blog
                ]);
            
            // users
            // TODO:
            
            // posts
            $posts = Post::factory()
                ->count(200)
                ->has(
                    PostVariant::factory()
                        ->count(2)
                        ->state(new Sequence(
                            ['language_id' => $english],
                            ['language_id' => $french]
                        ))
                        ->state(function() {
                            return [
                                'status' => collect(['draft', 'published', 'scheduled'])->random(),
                            ];
                        }),
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
            $posts->map(function($post) use ($tags) {

                PostTag::create([
                    'post_id' => $post->id,
                    'tag_id' => $tags->random()->id
                ]);
    
                // TODO:
                /*PostAuthor::create([
                    'post_id' => $post->id,
                    'user_id' => 1
                ]);*/
                
            });
            
            Navigation::factory()
                ->count(10)
                ->state(new Sequence(
                    ['type' => 'header'],
                    ['type' => 'footer'],
                ))
                ->create([
                    'blog_id' => $blog
                ]);
            
        }

        PostSearchRepository::setFilterableAttributes();
        PostSearchRepository::setSearchableAttributes();

        /*$this->call([
            BlogThemeFilesSeeder::class
        ]);*/

    }
    
}
