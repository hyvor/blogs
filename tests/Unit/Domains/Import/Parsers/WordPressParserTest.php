<?php

/*
namespace Tests\Unit\Import\Parsers;

use App\Stale\Import\Parsers\WordpressParser;

// php artisan test  --filter 'WordPressParserTest'
// If this test needs to work properly then we will have to add the html/body part to the filterXpath in the wordpress parser.

$file = <<<XML
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss>
            <channel>
                <title>Hyvor Blogs Blog</title>
                <language>en-US</language>
                <wp:author>
                    <wp:author_id>hi</wp:author_id>
                    <wp:author_login>bro</wp:author_login>
                    <wp:author_email>tec</wp:author_email>
                    <wp:author_display_name>poland</wp:author_display_name>
                    <wp:author_first_name>data</wp:author_first_name>
                    <wp:author_last_name>tora</wp:author_last_name>
                </wp:author>
                <wp:category>
                    <wp:term_id>4</wp:term_id>
                    <wp:category_nicename>alternatives</wp:category_nicename>
                    <wp:category_parent></wp:category_parent>
                    <wp:cat_name>Alternatives to WordPress</wp:cat_name>
                </wp:category>
                <item>
                    <title><![CDATA[Hello world!]]></title>
                    <link>https://blogs.hyvor.com/blog/?p=1</link>
                    <pubDate>Thu, 24 Feb 2022 07:21:13 +0000</pubDate>
                    <dc:creator><![CDATA[supun@hyvor.com]]></dc:creator>
                    <guid isPermaLink="false">http://blogs.hyvor.com/blog/?p=1</guid>
                    <description></description>
                    <content:encoded>
                        <![CDATA[<!-- wp:paragraph -->
                        <p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>
                         <!-- /wp:paragraph -->]]>
                    </content:encoded>
                    <excerpt:encoded><![CDATA[]]></excerpt:encoded>
                    <wp:post_id>1</wp:post_id>
                    <wp:post_date><![CDATA[2022-02-24 07:21:13]]></wp:post_date>
                    <wp:post_date_gmt><![CDATA[2022-02-24 07:21:13]]></wp:post_date_gmt>
                    <wp:post_modified><![CDATA[2022-02-24 10:00:05]]></wp:post_modified>
                    <wp:post_modified_gmt><![CDATA[2022-02-24 10:00:05]]></wp:post_modified_gmt>
                    <wp:ping_status><![CDATA[open]]></wp:ping_status>
                    <wp:post_name><![CDATA[hello-world__trashed]]></wp:post_name>
                    <wp:post_parent>0</wp:post_parent>
                    <wp:menu_order>0</wp:menu_order>
                    <wp:post_type><![CDATA[post]]></wp:post_type>
                    <wp:is_sticky>0</wp:is_sticky>
                    <category domain="category" nicename="uncategorized"><![CDATA[Uncategorized]]></category>
                </item>

                <item>
                    <title><![CDATA[About]]></title>
                    <link>https://blogs.hyvor.com/blog/?p=1</link>
                    <pubDate>Thu, 24 Feb 2022 07:21:13 +0000</pubDate>
                    <dc:creator><![CDATA[supun@hyvor.com]]></dc:creator>
                    <guid isPermaLink="false">http://blogs.hyvor.com/blog/?p=1</guid>
                    <description></description>
                    <content:encoded>
                        <![CDATA[<!-- wp:paragraph -->
                        <p>Welcome to hyvor. This is your first post. Edit or delete it, then start writing!</p>
                         <!-- /wp:paragraph -->]]>
                    </content:encoded>
                    <excerpt:encoded><![CDATA[]]></excerpt:encoded>
                    <wp:post_id>100</wp:post_id>
                    <wp:post_date><![CDATA[2022-02-24 07:21:13]]></wp:post_date>
                    <wp:post_date_gmt><![CDATA[2022-02-24 07:21:13]]></wp:post_date_gmt>
                    <wp:post_modified><![CDATA[2022-02-24 10:00:05]]></wp:post_modified>
                    <wp:post_modified_gmt><![CDATA[2022-02-24 10:00:05]]></wp:post_modified_gmt>
                    <wp:ping_status><![CDATA[open]]></wp:ping_status>
                    <wp:post_type><![CDATA[page]]></wp:post_type>
                    <wp:is_sticky>0</wp:is_sticky>
                    <category domain="category" nicename="uncategorized"><![CDATA[Uncategorized]]></category>
                </item>
            </channel>
        </rss>
XML;

it('parsers the language', function () use ($file) {
    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->lang as $lang) {
        $language = $lang['language'];
        $languageCode = $lang['languageCode'];
    }

    $this->assertEquals('English', $language);
});

it('parsers the authors', function () use ($file) {
    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->authors as $author) {
        $status = $author['status'];
        $role = $author['role'];
        $slug = $author['slug'];
        $email = $author['email'];
        $url = $author['url'];
        $name = $author['name'];
    }

    $this->assertEquals('Rasif', $name);
});


it('parsers the tags', function () use ($file) {
    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->tags as $tag) {
        $slug = $tag['slug'];
        $postsCount = $tag['postsCount'];
        $codeHead = $tag['codeHead'];
        $codeFoot = $tag['codeFoot'];
        $featuredImageUrl = $tag['featuredImageUrl'];
        $name = $tag['name'];
        $description = $tag['description'];
    }

    $this->assertEquals('alternatives', $name);
});

it('parsers the posts', function () use ($file) {
    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->posts as $post) {
        $slug = $post['slug'];
        $featuredImageUrl = $post['featuredImageUrl'];
        $canonicalUrl = $post['canonicalUrl'];
        $codeHead = $post['codeHead'];
        $codeFoot = $post['codeFoot'];
        $status = $post['status'];
        $title = $post['title'];
        $description = $post['description'];
        $tags = $post['tags'];
        $authors = $post['authors'];
        $content = $post['content'];
        $isFeatured = $post['isFeatured'];
    }

    $this->assertEquals('About', $title);
});*/
