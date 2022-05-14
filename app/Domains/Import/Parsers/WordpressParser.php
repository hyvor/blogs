<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;
use App\Domains\Import\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\import;


class WordpressParser implements ParserInterface
{    
    /**
    * @var array<array<string,mixed>>
    */
    public array $authorsArray = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $tagsArray = [];

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
            $createdAt = date("Y/m/d h:i:s");
            $updatedAt = date("Y/m/d h:i:s");

            $this->authorsArray[] = [$authorName => $authorId];
            // dd($this->authorsArray);

            $repo->author(
                id: $authorId,
                name: $authorName,
                role: $role,
                status: $status,
                slug: $slug,
                email: $authorEmail,
                createdAt: $createdAt,
                updatedAt: $updatedAt,
            );
        });

        
        // Tags section
        $data->filterXPath('rss/channel/wp:category')->each(function (Crawler $node, $i) use ($repo) {

            $tagId = $node->children('wp|term_id')->text('null');
            $tagName = $node->children('wp|cat_name')->text('null');
            $slug = Str::slug($tagName.rand());

            $createdAt = date("Y/m/d h:i:s");
            $updatedAt = date("Y/m/d h:i:s");

            $this->tagsArray[] = [$tagName => $tagId];
            // dump($this->tagsArray);

            $repo->tag(
                id: $tagId,
                name: $tagName,
                slug: $slug,
                createdAt: $createdAt,
                updatedAt: $updatedAt,
            );
        });

        
        // Post section
        $data->filterXPath('rss/channel/item[wp:post_type="post"]')->each(function (Crawler $node, $i) use ($repo) {

            $postId = $node->children('wp|post_id')->text('null');
            $title = $node->filter('title')->text('null');
            $createdAt = $node->filter('wp|post_date')->text('null');
            $description = $node->children('description')->text('null');
            $tags = $node->children('category')->extract(['_text']);
            $postStatus = $node->filter('wp|status')->text('null');
            $postContent = $node->children('content|encoded')->text('null');
            $authors = $node->children('dc|creator')->extract(['_text']);

            $publishedAt = date("Y/m/d h:i:s");

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
            $isPage = false;

            foreach($this->tagsArray as $tagData) {
                foreach($tagData as $tagKey=>$tagValue){
                    foreach($tags as $tag) {
                        if( $tagKey == $tag){
                            $tagIds[] = $tagValue;
                        }
                    }
                }
            }

            foreach($this->authorsArray as $authorData) {
                foreach($authorData as $authorKey=>$authorValue){
                    foreach($authors as $author) {
                        if( $authorKey == $author){
                            $authorIds[] = $authorValue;
                        }
                    }
                }
            }

            $repo->post(
                id: $postId,
                isPage: $isPage,
                title: $title,
                description: $description,
                tags: $tagIds,
                authors: $authorIds,
                status: $postStatus,
                slug: $slug,
                createdAt: $createdAt,
                updatedAt: $createdAt,
                publishedAt: $publishedAt,
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

            $publishedAt = date("Y/m/d h:i:s");

            if (strlen($description) > config('limits.max_post_description_length')) {
                $description = substr($description, 0, config('limits.max_post_description_length'));
            }

            if (strlen($title) > config('limits.max_post_title_length')) {
                $title = substr($title, 0, config('limits.max_post_title_length'));
            }

            $slug = Str::slug($title);
            $isPage = true;

            foreach($this->authorsArray as $authorData) {
                foreach($authorData as $authorKey=>$authorValue){
                    foreach($authors as $author) {
                        if( $authorKey == $author){
                            $authorIds[] = $authorValue;
                        }
                    }
                }
            }

            $repo->post(
                id: $postId,
                isPage: $isPage,
                title: $title,
                description: $description,
                authors: $authorIds,
                status: $pageStatus,
                slug: $slug,
                createdAt: $created_at,
                updatedAt: $created_at,
                publishedAt: $publishedAt,
                content: $pageContent,
            );
        });

        return $repo;
    }
}