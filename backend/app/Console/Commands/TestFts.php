<?php

namespace App\Console\Commands;

use App\Domains\Post\PostSearchRepository;
use Illuminate\Console\Command;
use App\Models\Blog;
use App\Http\Controllers\DataApi\Helper;
use Illuminate\Support\Facades\DB;

class TestFts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test_fts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PSQL FTS on 100k posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::disableQueryLog();

        // Using factories
        /*
        $blogs = Blog::factory()
        ->count(1)
        ->create();

        foreach ($blogs as $blog) {

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
        }

        $posts = Post::factory()
            ->count(100000)
            ->has(
                PostVariant::factory()
                    ->count(2)
                    ->state(new Sequence(
                        ['language_id' => 0],
                        ['language_id' => 1]
                    ))
                    ->state(new Sequence(
                        ['status' => 'draft'],
                        ['status' => 'published'],
                        ['status' => 'scheduled']
                    )),
                'variants'
            )
            ->create([
                'blog_id' => $blogs[0],
            ]);

        */

        // Using raw queries
        $nb = rand(0, 100000);

        $blogId = DB::table('blogs')->insertGetId([
            'subdomain' => "test {$nb}",
            'trial_ends_at' => now()->addDays(14),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Insert languages for the blog
        $englishId = DB::table('languages')->insertGetId([
            'blog_id' => $blogId,
            'code' => 'en',
            'name' => 'English',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $frenchId = DB::table('languages')->insertGetId([
            'blog_id' => $blogId,
            'code' => 'fr',
            'name' => 'French',
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Insert blog variants
        DB::table('blog_variants')->insert([
            [
                'blog_id' => $blogId,
                'language_id' => $englishId,
            ],
            [
                'blog_id' => $blogId,
                'language_id' => $frenchId,
            ],
        ]);
        
        // Insert posts
        for ($i = 0; $i < 100000; $i++) {
            $postId = DB::table('posts')->insertGetId([
                'blog_id' => $blogId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        
            // Insert post variants for the post
            DB::table('post_variants')->insert([
                [
                    'post_id' => $postId,
                    'language_id' => $englishId,
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'post_id' => $postId,
                    'language_id' => $frenchId,
                    'status' => 'published',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $blog = Blog::find($blogId);

        $language = Helper::getLanguage($blog, code: null);
        $startTime = microtime(true);
        $search = PostSearchRepository::search($blog, $language, 'hello', 30, 0, false);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        echo "Execution time: " . $executionTime . " seconds\n";
    }
}
