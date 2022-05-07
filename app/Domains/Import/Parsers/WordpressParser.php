<?php

namespace App\Domains\Import\Parsers;

use App\Domains\Import\Repository;
use App\Models\Blog;
use App\Domains\Import\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class WordpressParser implements ParserInterface
{
    public $repo;
    
    public function __construct(public string $file)
    {
        $this->file = $file;
    }

    public function parse() : Repository
    {
        $this->repo = new Repository();
        $data = new Crawler($this->file);

        // language section
        $language = $data->filterXPath('rss/channel/language')->text();

        $this->repo->language(
            language: $language,
        );

        // Authors section
        $data->filterXPath('rss/channel/wp:author')->each(function (Crawler $node, $i) {
            $authorId = $node->children('wp|author_id')->extract(['_text']);
            $authorName = $node->children('wp|author_login')->extract(['_text']);
            $authorEmail = $node->children('wp|author_email')->extract(['_text']);

            $tagCount = count($authorEmail);
            // dd($authorId);

            $this->repo->author(
                id:$authorId,
                name : $authorName,
                email :$authorEmail,
            );
        });

        // Tags section
        $data->filterXPath('rss/channel/wp:category')->each(function (Crawler $node, $i) {
            $tagId = $node->children('wp|term_id')->extract(['_text']);
            $tagName = $node->children('wp|cat_name')->extract(['_text']);

            $this->repo->tag(
                id:$tagId,
                name : $tagName,
            );
        });

        // Post section
        $data->filterXPath('rss/channel/item[wp:post_type="post"]')->each(function (Crawler $node, $i) {

            $postId = $node->children('wp|post_id')->extract(['_text']);
            $title = $node->filter('title')->extract(['_text']);
            $createdAt = $node->filter('wp|post_date')->extract(['_text']);
            $postType = $node->children('wp|post_type')->extract(['_text']);
            $description = $node->children('description')->extract(['_text']);
            $category = $node->filter('category')->extract(['_text']);
            $postStatus = $node->filter('wp|status')->extract(['_text']);
            $postContent = $node->children('content|encoded')->extract(['_text']);

            // Repository::post($postId, $title, $createdAt, $postType, $description, $category, $postStatus, $postContent);

            $this->repo->post(
                id:$postId,
                title : $title,
                createdAt:$createdAt,
                postType : $postType,
                description:$description,
                category : $category,
                status:$postStatus,
                content : $postContent,
            );
        });

        // Page section
        $data->filterXPath('rss/channel/item[wp:post_type="page"]')->each(function (Crawler $node, $i) {

            $postId = $node->children('wp|post_id')->extract(['_text']);
            $title = $node->filter('title')->extract(['_text']);
            $createdAt = $node->filter('wp|post_date')->extract(['_text']);
            $postType = $node->children('wp|post_type')->extract(['_text']);
            $description = $node->children('description')->extract(['_text']);
            $category = $node->filter('category')->extract(['_text']);
            $postStatus = $node->filter('wp|status')->extract(['_text']);
            $postContent = $node->children('content|encoded')->extract(['_text']);

            // Repository::page($postId, $title, $createdAt, $postType, $description, $category, $postStatus, $postContent);

            $this->repo->page(
                id:$postId,
                title : $title,
                createdAt:$createdAt,
                postType : $postType,
                description:$description,
                category : $category,
                status:$postStatus,
                content : $postContent,
            );

        });










        // tags
        // $tagNames = $data->filterXPath('rss/channel/wp:category/wp:cat_name')->each(function (Crawler $node, $i) {
        //     return $node->text('There is no tag name');
        // });



        // 99% correct but some are not working
        // $post = $data->filterXPath('rss/channel')->children('item')->each(function (Crawler $node, $i) {
        //     $item = array (
        //         'postId' => $node->children('wp|post_id')->text(),
        //         'title' => $node->children('title')->text(),
        //         'postType' => $node->children('wp|post_type')->text('null'),
        //         'postContent' => $node->children('content|encoded')->text(),
        //         'postedAt' => $node->children('wp|post_date')->text(),
        //         'description' => $node->children('description')->text(null),
        //     );
        //     return $item;
        // });

        // Perfect method but in this method the way the data is been fetch has an issue.
        // $posts = $data->filterXPath('rss/channel/item[wp:post_type="post"]')->each(function (Crawler $node, $i) {
        //     $item = array (
        //         'postId' => $node->children('wp|post_id')->extract(['_text']),
        //         'title' => $node->filter('title')->extract(['_text']),
        //         'createdAt' => $node->filter('wp|post_date')->extract(['_text']),
        //         'postType' => $node->children('wp|post_type')->extract(['_text']),
        //         'description' => $node->children('description')->extract(['_text']),
        //         'category' => $node->filter('category')->extract(['_text']),
        //         'postStatus' => $node->filter('wp|status')->extract(['_text']),
        //         'postContent' => $node->children('content|encoded')->extract(['_text']),
        //     );
        //     return $item;
        // });

        // $postCount = count($posts);
        // dd($posts);

        // $repo->postData(
        //     posts: $posts,
        //     postCount : $postCount,
        // );















        // $rss = $data->filterXPath('rss')->each(function (Crawler $node, $i) {
        //     // return $node->children('rss/channel/item[wp:post_type="post"]');
        //     $item = array (
        //         'postId' => $node->filterXPath('rss/channel')->children('item')->each(function (Crawler $node, $i) {
        //                return $node->children('title')->text();
        //             }),

        //         // 'children' => $node->filterXPath('rss/channel')->children('item')->text(),
        //         // 'previousAll' => $node->filterXPath('rss/channel')->previousAll(),
        //     );
        //     return $item;
        // });


        // $test = $data->filterXPath('rss/channel/item')->eq(10);
        // dd($test);

        // $rss = $data->filterXPath('rss')->each(function (Crawler $node, $i) {
        //     // return $node->children('rss/channel/item[wp:post_type="post"]');
        //     $item = array (
        //         // 'postId' => $node->children('wp:post_id')->text(),
        //         'title' => $node->children('title')->text(),
        //         // 'postType' => $node->children('wp:post_type')->text('null'),
        //         // 'postContent' => $node->children('content:encoded')->text(),
        //         // 'postedAt' => $node->children('wp:post_date')->text(),
        //         // 'description' => $node->children('description')->text(null),

        //         // 'postAuthor' => $node->children('dc:creator')->text('creator null'),
        //         // 'test2' => $node->filterXPath('rss/channel/item/content:encoded')->text('Default text content'),
        //         // 'description' => $node->filterXPath('rss/channel/item/description')->text('Default text content'),

        //         // 'postCategory' => $node->children('category')->text(),
                
        //         // 'children' => $node->filterXPath('rss/channel')->children('item')->text(),
        //         // 'previousAll' => $node->filterXPath('rss/channel')->previousAll(),
        //     );
        //     return $item;
        // });

        // dd($rss);

        // $rss2 = $data->filterXPath('item')->each(function (Crawler $node, $i) {
        //     $item = array (
        //         'postId' => $node->filterXPath('rss/channel/item[wp:post_type="post"]/wp:post_id')->each(function (Crawler $node2, $i) {
        //             return $node2->text('There is no id');
        //          }),
        //         // 'title' => $node->filterXPath('title')->nodeValue,
        //         // 'link' => $node->filterXPath('link')->nodeValue,
        //         // 'pubDate' => $node->filterXPath('pubDate')->nodeValue,
        //         // 'description' => $node->filterXPath('description')->nodeValue,
        //         // 'creator' => $node->filterXPath('creator')->nodeValue,
        //         // 'post_modified' => $node->filterXPath('post_modified')->nodeValue,
        //         // 'content' => trim(strip_tags($node->filterXPath('encoded')->nodeValue))
        //         );

        //     return $item;
        // });

        // $xml = simplexml_load_file($this->file);
        // foreach ($xml->rss as $el) {
        //     echo $el->name;
        // }

        // dd($item);

        // $data->filterXPath('rss/channel/item')->each(function (Crawler $parentCrawler, $i) {
        
        //     // $item = $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //     //     return $node->nodeValue->text();
        //     // });


        //     $item = array (
        //     'postId' => $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),
        //     'title' => $parentCrawler->filterXPath('rss/channel/item/title')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),
        //     );

        //     dd($item);
        // });


        // This was almost done.
        // $postData = $data->filterXPath('rss/channel/item')->each(function (Crawler $parentCrawler, $i) {

        //     $item = array (
        //     // 'postId' => $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //     //        return $node->text();
        //     //     }),
        //     // 'title' => $parentCrawler->filterXPath('rss/channel/item/title')->each(function (Crawler $node, $i) {
        //     //         return $node->text();
        //     //     }),

        //     'postId' => $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->text('Default text content'),
        //     );

        //     return $item;
        // });


        // dd($data);

        // $postData = $data->filterXPath('rss')->each(function (Crawler $parentCrawler, $i) {

        //     // dd($parentCrawler);
        //     // $item = $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->text('Default text content');
        //     // [not(wp:post_type=attachment)]
        //     $item = array (
        //     'postId' => $parentCrawler->filterXPath('rss/channel/item[wp:post_type="post"]/dc:creator')->each(function (Crawler $node, $i) {
        //            return $node->text('There is no id');
        //         }),
        //     'title' => $parentCrawler->filterXPath('rss/channel/item[wp:post_type="post"]/title')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),

        //     // 'postId' => $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->text('There is no id.'),
        //     // 'title' => $parentCrawler->filterXPath('rss/channel/item/title')->text('Default text content'),
        //     // 'postType' => $parentCrawler->filterXPath('rss/channel/item/wp:post_type')->text('Default text content'),
        //     // 'postContent' => $parentCrawler->filterXPath('rss/channel/item/content:encoded')->text('Default text content'),
        //     // 'postedAt' => $parentCrawler->filterXPath('rss/channel/item/wp:post_date')->text('Default text content'),
        //     // 'description' => $parentCrawler->filterXPath('rss/channel/item/description')->text('Default text content'),
        //     // 'postAuthor' => $parentCrawler->filterXPath('rss/channel/item/dc:creator')->text('Default text content'),
        //     // 'postCategory' => $parentCrawler->filterXPath('rss/channel/item/category')->text('Default text content'),

        //     );

        //     return $item;
        // });

        // dd($postData);
        
        // for (i = 0; i <x.length; i++) {
            // do something for each node
        // }

        // dump($postData);

        // $postData = $data
        //     ->filterXpath('rss/channel/item/')
        //     ->extract(['_name', '_text', 'class']);
        // $dd = $data->filterXPath('rss/channel/item/title')->text('Default text content');



        // $postData = $data->each(function (Crawler $parentCrawler, $i) {

        //     $item = array (
        //     'postId' => $parentCrawler->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //            return $node->text();
        //         }),
        //     'title' => $parentCrawler->filterXPath('rss/channel/item/title')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),
        //     );

        //     return $item;
        // });


        // Almost correct method
        // $data->filterXPath('rss/channel/item')->each(function (Crawler $crawler, $i) {
        //     foreach ($crawler as $domElement) {

        //         dump($domElement);
        //     }
        //     // return $node->text();
        // });



        // $crawler = $data
        //     ->filterXPath('rss/channel/item')
        //     ->reduce(function (Crawler $node, $i) {
        //         // filters every other node
        //         $toast = $node->filterXPath('rss/channel/item');
        //         dump($toast);
        //         // return ($node % 2) == 0;
        //     });

        // $crawler = $data->filter('rss/channel/item')->children();

        // dump($crawler);



        // $data->filterXPath('rss/channel/item/wp:post_id')->text('Default text content')
        // foreach ($data as $domElement) {
        //     dd($domElement->nodeName);
        //     $item = array (
        //         'postId' => $data->filterXPath('rss/channel/item/wp:post_id')->text('Default text content'),
        //     );
        // }

        // $item = array (
        //     'postId' => $data->filterXPath('rss/channel/item/wp:post_id')->text('Default text content'),
        //     // 'title' => $data->filterXPath('rss/channel/item/title')->each(function (Crawler $node, $i) {
        //     //         return $node->text();
        //     //     }),
        //     );

        // dump($item);



        // foreach ($data->filterXPath('item') as $node) {
        //     $item = array (
        //         'title' => $node->filterXPath('title')->text()->nodeValue,
        //         'link' => $node->filterXPath('link')->nodeValue,
        //         );
        // }


        // $item = array (
        //     'postId' => $data->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),
        //     'title' => $data->filterXPath('rss/channel/item/title')->each(function (Crawler $node, $i) {
        //             return $node->text();
        //         }),
        //     );

        //     dd($item);


        // $days = $data->filterXPath('rss/channel/item');

        // $days->each(function (Crawler $day) {
        //     $rows = $day->filterXPath('/wp:post_id');
        //     $rows->each(function (Crawler $row) {
        //         // $cells = $row->filterXPath('td');
        //         // $cells->each(function (Crawler $cell) {
        //         //     dump($cell->text());
        //         // });
        //         return $row->text();
        //     });
        // });

        // dd($days);

        // $postIds = $data->filterXPath('rss/channel/item/wp:post_id')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postTitles = $data->filterXPath('rss/channel/item[wp:post_type="post"]/title')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postType = $data->filterXPath('rss/channel/item/wp:post_type')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postContent = $data->filterXPath('rss/channel/item/content:encoded')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postedAt = $data->filterXPath('rss/channel/item/wp:post_date')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postDescription = $data->filterXPath('rss/channel/item/description')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postAuthor = $data->filterXPath('rss/channel/item/dc:creator')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });

        // $postCategory = $data->filterXPath('rss/channel/item/category')->each(function (Crawler $node, $i) {
        //     return $node->text();
        // });











        dd('$postAuthor');

        return $this->repo;
    }
}