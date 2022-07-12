<?php

namespace Tests\Unit\Import\Parsers;

use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Repository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;

test('testing users and users varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
<wp:author>
    <wp:author_id>1</wp:author_id>
    <wp:author_login><![CDATA[supun@hyvor.com]]></wp:author_login>
    <wp:author_email><![CDATA[supun@hyvor.com]]></wp:author_email>
    <wp:author_display_name><![CDATA[Supun kavinda]]></wp:author_display_name>
    <wp:author_first_name><![CDATA[supun]]></wp:author_first_name>
    <wp:author_last_name><![CDATA[kavinda]]></wp:author_last_name>
</wp:author>
</channel>

</rss>
XML;

$parser = new WordpressParser($str);
$repo = new Repository();
$data = new Crawler($str);
$repo = invade($parser)->parseAuthors($repo,$data);

$user = $repo->user[0];

expect($user->id)->toBe(1);
expect($user->blog_id)->toBe(1);
expect($user->email)->toBe('supun@hyvor.com');

$userVariant = $repo->userVariant[0];

expect($userVariant->id)->toBe(1);
expect($userVariant->user_id)->toBe(1);
expect($userVariant->name)->toBe('Supun kavinda');

})->group('userParser');


test('testing tags and tags varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
    <wp:category>
        <wp:term_id>50</wp:term_id>
        <wp:category_nicename><![CDATA[announcements]]></wp:category_nicename>
        <wp:category_parent><![CDATA[]]></wp:category_parent>
        <wp:cat_name><![CDATA[Announcements]]></wp:cat_name>
    </wp:category>
</channel>

</rss>
XML;

$parser = new WordpressParser($str);
$repo = new Repository();
$data = new Crawler($str);
$repo = invade($parser)->parseTags($repo,$data);

$tag = $repo->tag[0];

expect($tag->id)->toBe(50);
expect($tag->blog_id)->toBe(1);

$tagVariant = $repo->tagVariant[0];

expect($tagVariant->id)->toBe(50);
expect($tagVariant->tag_id)->toBe(50);
expect($tagVariant->name)->toBe('Announcements');

})->group('tagParser');


test('testing post and post varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
    <wp:author>
        <wp:author_id>1</wp:author_id>
        <wp:author_login><![CDATA[supun@hyvor.com]]></wp:author_login>
        <wp:author_email><![CDATA[supun@hyvor.com]]></wp:author_email>
        <wp:author_display_name><![CDATA[Supun kavinda]]></wp:author_display_name>
        <wp:author_first_name><![CDATA[supun]]></wp:author_first_name>
        <wp:author_last_name><![CDATA[kavinda]]></wp:author_last_name>
    </wp:author>
     <wp:category>
        <wp:term_id>50</wp:term_id>
        <wp:category_nicename><![CDATA[announcements]]></wp:category_nicename>
        <wp:category_parent><![CDATA[]]></wp:category_parent>
        <wp:cat_name><![CDATA[Announcements]]></wp:cat_name>
    </wp:category>
   <item>
        <title><![CDATA[Adding Comments to Static Sites]]></title>
        <link>https://talk.hyvor.com/blog/comments-for-static-sites/</link>
        <pubDate>Fri, 31 Jan 2020 05:42:44 +0000</pubDate>
        <dc:creator><![CDATA[supun]]></dc:creator>
        <guid isPermaLink="false">https://talk.hyvor.com/blog/?p=273</guid>
        <description>Testing Desc</description>
        <content:encoded><![CDATA[matching string]]></content:encoded>
        <excerpt:encoded><![CDATA[]]></excerpt:encoded>
        <wp:post_id>273</wp:post_id>
        <wp:post_date><![CDATA[2020-01-31 05:42:44]]></wp:post_date>
        <wp:post_date_gmt><![CDATA[2020-01-31 05:42:44]]></wp:post_date_gmt>
        <wp:post_modified><![CDATA[2021-08-03 16:59:56]]></wp:post_modified>
        <wp:post_modified_gmt><![CDATA[2021-08-03 16:59:56]]></wp:post_modified_gmt>
        <wp:comment_status><![CDATA[open]]></wp:comment_status>
        <wp:ping_status><![CDATA[open]]></wp:ping_status>
        <wp:post_name><![CDATA[comments-for-static-sites]]></wp:post_name>
        <wp:status><![CDATA[publish]]></wp:status>
        <wp:post_parent>0</wp:post_parent>
        <wp:menu_order>0</wp:menu_order>
        <wp:post_type><![CDATA[post]]></wp:post_type>
        <wp:post_password><![CDATA[]]></wp:post_password>
        <wp:is_sticky>0</wp:is_sticky>
    </item>
</channel>

</rss>
XML;

$parser = new WordpressParser($str);
$repo = new Repository();
$data = new Crawler($str);

$repo = invade($parser)->parsePosts($repo,$data);

$post = $repo->post[0];

expect($post->id)->toBe(273);
expect($post->blog_id)->toBe(1);

$postVariant = $repo->postVariant[0];


expect($postVariant->id)->toBe(273);
expect($postVariant->post_id)->toBe(273);
expect($postVariant->status->value)->toBeIn(['draft','published','scheduled']);
expect($postVariant->title)->toBe('Adding Comments to Static Sites');
expect($postVariant->description)->toBe('Testing Desc');

expect($postVariant->content)->toBeJson();
$content = json_decode($postVariant->content);


$postContent = 'matching string';

//dd($postContent);

expect($content->content[0]->content[0]->text)->toBe($postContent);

})->group('postParser');



it('parsers the language', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));
    
    $parser = new WordpressParser($file);
    
    $repo = new Repository();
    $data = new Crawler($file);

    $repo = invade($parser)->parseLang($repo,$data);

    foreach ($repo->lang as $lang) {
        $language = $lang['language'];
        $languageCode = $lang['languageCode'];
    }

    $this->assertEquals('English', $language);

})->group('langParser');





