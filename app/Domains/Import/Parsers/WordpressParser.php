<?php

namespace App\Domains\Import\Parsers;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Import\Repository;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;

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

    }

    public function parse(): Repository{
        
        $repo = new Repository();
        $data = new Crawler($this->file);

        $this->parseLang($repo,$data);
        $this->parseAuthors($repo,$data);
        $this->parseTags($repo,$data);
        $this->parsePosts($repo,$data);

        return $repo;
        
    }

    private function parseLang($repo,$data){

        $language = '';
        $languageCode = '';

        if(!empty($data->filterXPath('rss/channel/language'))){
            $languageCode = $data->filterXPath('rss/channel/language')->text();
            $language = locale_get_display_language($languageCode);

        }

        $repo->language(
        
            language: $language,
            languageCode: $languageCode,
        );

        return $repo;
    }

    private function parseAuthors($repo,$data){

         // Authors section
            $data->filterXPath('rss/channel/wp:author')->each(function (Crawler $node, $i) use ($repo) {
               
                $authorId = (int)$node->children('wp|author_id')->text('empty');
                $authorName = $node->children('wp|author_display_name')->text('empty');
                $authorEmail = $node->children('wp|author_email')->text('empty');

                $role = UserRoleEnum::from('editor');
                $status = UserStatusEnum::from('active');
                $slug = Str::slug($authorName.rand());
                $createdAt = date("Y/m/d h:i:s");
                $updatedAt = date("Y/m/d h:i:s");

                $this->authorsArray[] = [$authorName => $authorId];
                
                $user = new User();
                $user->id = (int)$authorId;
                $user->blog_id = 1;
                $user->email = $authorEmail;
                $user->role = $role;
                $user->status = $status;
                $user->slug = $slug;
                $user->created_at = $createdAt;
                $user->updated_at = $updatedAt;

                $repo->user($user);

                $userVariant = new UserVariant();
                $userVariant->id = (int)$authorId;
                $userVariant->user_id = (int)$authorId;
                $userVariant->name = $authorName;
    
                $repo->userVariant($userVariant);
            });   

            return $repo;  
    }

    private function parseTags($repo,$data){

         // Tags section
            $data->filterXPath('rss/channel/wp:category')->each(function (Crawler $node, $i) use ($repo) {
                $tagId = $node->children('wp|term_id')->text('null');
                $tagName = $node->children('wp|cat_name')->text('null');
                $slug = Str::slug($tagName.rand());

                $createdAt = date("Y/m/d h:i:s");
                $updatedAt = date("Y/m/d h:i:s");

                $this->tagsArray[] = [$tagName => $tagId];
                // dump($this->tagsArray);

                $tags = new Tag();

                $tags->id = (int)$tagId;
                $tags->blog_id = 1;
                $tags->slug = $slug;
                $tags->posts_count = 0;
                $tags->created_at = $createdAt;
                $tags->updated_at = $updatedAt;

                $repo->tag($tags);

                $tagVariant = new TagVariant();

                $tagVariant->id = (int)$tagId;
                $tagVariant->tag_id = (int)$tagId;    
                $tagVariant->name = $tagName;
                $tagVariant->created_at = $createdAt;
                $tagVariant->updated_at = $updatedAt;

                $repo->tagVariant($tagVariant);

            });

            return $repo;  
    }


    private function parsePosts($repo,$data){

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
                
                }else if ($postStatus == 'publish') {
                   
                    $postStatus = 'published';
                
                }else
                    $postStatus = 'draft';

                $slug = Str::slug($title).rand();
                $isPage = false;


                foreach ($this->tagsArray as $tagData) {
                    foreach ($tagData as $tagKey => $tagValue) {
                        foreach ($tags as $tag) {
                            if ($tagKey == $tag) {
                                $tagIds[] = $tagValue;
                            }
                        }
                    }
                }

                foreach ($this->authorsArray as $authorData) {
                    foreach ($authorData as $authorKey => $authorValue) {
                        foreach ($authors as $author) {
                            if ($authorKey == $author) {
                                $authorIds[] = $authorValue;
                            }
                        }
                    }
                }


                $jsonPost = json_encode([
                    
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'custom_html',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => (string)$postContent,
                                ],
                            ],
                        ],
                    ],
                ]);

                
                $post = new Post();
                $post->id = (int)$postId;
                $post->blog_id = 1;
                $post->is_page = $isPage;
                $post->is_featured = 0;
                $post->slug = $slug;
                $post->created_at = $createdAt;
                $post->updated_at = $createdAt;
                $post->published_at = $publishedAt;

                $repo->post($post);

                $postVariant = new PostVariant();

                $postVariant->id = (int)$postId;
                $postVariant->post_id = (int)$postId;
                $postVariant->status = $postStatus;
                $postVariant->content = $jsonPost;
                $postVariant->title = $title;
                $postVariant->description = $description;

                $repo->postVariant($postVariant);

            });

            // Page section
            $data->filterXPath('rss/channel/item[wp:post_type="page"]')->each(function (Crawler $node, $i) use ($repo) {
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

                if ($pageStatus === 'future' || $pageStatus == 'pending' || $pageStatus == 'trash' || $pageStatus == 'auto-draft' || $pageStatus == 'inherit' || $pageStatus == 'new') {
                    $pageStatus = 'draft';
                
                }else if ($pageStatus == 'publish') {
                   
                    $pageStatus = 'published';
                
                }else
                    $pageStatus = 'draft';


                $slug = Str::slug($title).rand();
                $isPage = true;

                foreach ($this->authorsArray as $authorData) {
                    foreach ($authorData as $authorKey => $authorValue) {
                        foreach ($authors as $author) {
                            if ($authorKey == $author) {
                                $authorIds[] = $authorValue;
                            }
                        }
                    }
                }

                $jsonPost = json_encode([
                    
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'custom_html',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => (string)$pageContent,
                                ],
                            ],
                        ],
                    ],
                ]);

                
                $post = new Post();

                $post->blog_id = 1;
                $post->is_page = $isPage;
                $post->is_featured = 0;
                $post->slug = $slug;
                $post->created_at = $created_at;
                $post->updated_at = $created_at;
                $post->published_at = $publishedAt;

                $repo->post(post:$post);

                $postVariant = new PostVariant();

                $postVariant->id = (int)$postId;
                $postVariant->post_id = (int)$postId;
                $postVariant->status = $pageStatus;
                $postVariant->content = $jsonPost;
                $postVariant->title = $title;
                $postVariant->description = $description;

                $repo->postVariant(postVariant:$postVariant);

            });

            return $repo;
    }

   
}
