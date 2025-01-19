<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\PostVariant;
use Illuminate\Console\Command;
use App\Models\Blog;
use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Post\Content\PostContentService;

class AddFtsAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:add-fts-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add and init all necessary attributes for PSQL FTS search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $blogs = Blog::all();

        foreach ($blogs as $blog) {
            $postsids = $blog->posts->pluck('id')->toArray();
            $post_variants = PostVariant::whereIn('post_id', $postsids)->get();
            foreach ($post_variants as $post_variant) {
                $language = Language::find($post_variant->language_id);
                
                $text = PostContentService::getText($post_variant->content, $blog);
                $post_variant->update([
                    'ts_language' => VariantsHelper::getVariantTsLanguage($language),
                    'content_text' => $text
                ]);
            }
        }
    }
}
