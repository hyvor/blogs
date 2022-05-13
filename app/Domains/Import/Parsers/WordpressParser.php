<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;
use App\Domains\Import\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use Illuminate\Support\Str;

class WordpressParser implements ParserInterface
{    
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse() : Repository
    {
        $repo = new Repository();
        $data = new Crawler($this->file);

        $languageCode = $data->filterXPath('rss/channel/language')->text();
        $language = locale_get_display_language($languageCode);

        $repo->language(
            language: $language,
            languageCode: $languageCode,
        );

        // Authors section
        $data->filterXPath('rss/channel/wp:author')->each(function (Crawler $node, $i) use ($repo) {

            $authorId = $node->children('wp|author_id')->text('empty');
            $authorName = $node->children('wp|author_login')->text('empty');
            $authorEmail = $node->children('wp|author_email')->text('empty');

            $role = UserRoleEnum::from('editor');
            $status = UserStatusEnum::from('active');
            $slug = Str::slug($authorName.rand());
            $created_at = date("Y/m/d h:i:s");
            $updated_at = date("Y/m/d h:i:s");

            $repo->author(
                id: $authorId,
                name: $authorName,
                role: $role,
                status: $status,
                slug: $slug,
                email: $authorEmail,
                created_at: $created_at,
                updated_at: $updated_at,
            );
        });

        // Tags section
        $data->filterXPath('rss/channel/wp:category')->each(function (Crawler $node, $i) use ($repo) {

            $tagId = $node->children('wp|term_id')->text('null');
            $tagName = $node->children('wp|cat_name')->text('null');
            $slug = Str::slug($tagName.rand());

            $created_at = date("Y/m/d h:i:s");
            $updated_at = date("Y/m/d h:i:s");

            $repo->tag(
                id: $tagId,
                name: $tagName,
                slug: $slug,
                created_at: $created_at,
                updated_at: $updated_at,
            );
        });

        // Post section
        $data->filterXPath('rss/channel/item[wp:post_type="post"]')->each(function (Crawler $node, $i) use ($repo) {

            $postId = $node->children('wp|post_id')->text('null');
            $title = $node->filter('title')->text('null');
            $created_at = $node->filter('wp|post_date')->text('null');
            $description = $node->children('description')->text('null');
            $tags = $node->children('category')->extract(['_text']);
            $postStatus = $node->filter('wp|status')->text('null');
            $postContent = $node->children('content|encoded')->text('null');
            $authors = $node->children('dc|creator')->extract(['_text']);

            $published_at = date("Y/m/d h:i:s");

            if (mb_strlen($description) > config('limits.max_post_description_length')) {
                $description = substr($description, 0, config('limits.max_post_description_length'));
                // $endPoint = strrpos($stringCut, ' ');
                // $description = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
            }

            if (mb_strlen($title) > config('limits.max_post_title_length')) {
                $title = substr($title, 0, config('limits.max_post_title_length'));
            }

            if ($postStatus === 'future' || $postStatus == 'pending' || $postStatus == 'trash' || $postStatus == 'auto-draft' || $postStatus == 'inherit' || $postStatus == 'new') {
                $postStatus = 'draft';
            }

            if($postStatus == 'publish'){
                $postStatus = 'published';
            }

            $slug = Str::slug($title);
            $is_page = false;

            $repo->post(
                id: $postId,
                is_page: $is_page,
                title: $title,
                description: $description,
                tags: $tags,
                authors: $authors,
                status: $postStatus,
                slug: $slug,
                created_at: $created_at,
                updated_at: $created_at,
                published_at: $published_at,
                content: $postContent,
            );
        });

        // Page section
        $data->filterXPath('rss/channel/item[wp:post_type="page"]')->each(function (Crawler $node, $i) use($repo) {

            $postId = $node->children('wp|post_id')->text('null');
            $title = $node->filter('title')->text('null');
            $created_at = $node->filter('wp|post_date')->text('null');
            $description = $node->children('description')->text('null');
            $tags = $node->children('category')->extract(['_text']);
            $pageStatus = $node->filter('wp|status')->text('null');
            $pageContent = $node->children('content|encoded')->text('null');
            $authors = $node->children('dc|creator')->extract(['_text']);

            $published_at = date("Y/m/d h:i:s");

            if (strlen($description) > config('limits.max_post_description_length')) {
                $description = substr($description, 0, config('limits.max_post_description_length'));
            }

            if (strlen($title) > config('limits.max_post_title_length')) {
                $title = substr($title, 0, config('limits.max_post_title_length'));
            }

            $slug = Str::slug($title);
            $is_page = true;

            $repo->page(
                id: $postId,
                is_page: $is_page,
                title: $title,
                description: $description,
                tags: $tags,
                authors: $authors,
                status: $pageStatus,
                slug: $slug,
                created_at: $created_at,
                updated_at: $created_at,
                published_at: $published_at,
                content: $pageContent,
            );
        });

        return $repo;
    }
}