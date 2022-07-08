<?php

namespace Tests\Unit\Import\Parsers;

use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Importer;
use App\Models\Blog;
use App\Models\Import;
use Symfony\Component\DomCrawler\Crawler;
use App\Domains\Import\Repository;

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
                                        <category domain="category" nicename="comments"><![CDATA[Comments]]></category>
        <category domain="category" nicename="static-sites"><![CDATA[Static Sites]]></category>
                        <wp:postmeta>
        <wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
        <wp:meta_value><![CDATA[3]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_yoast_wpseo_primary_category]]></wp:meta_key>
        <wp:meta_value><![CDATA[17]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_yoast_wpseo_focuskw]]></wp:meta_key>
        <wp:meta_value><![CDATA[comments for static sites]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_yoast_wpseo_linkdex]]></wp:meta_key>
        <wp:meta_value><![CDATA[80]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_yoast_wpseo_content_score]]></wp:meta_key>
        <wp:meta_value><![CDATA[90]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_yoast_wpseo_metadesc]]></wp:meta_key>
        <wp:meta_value><![CDATA[Need to add comments to your static sites? For sure, Hyvor Talk is the best option available for that. Learn how to add comments to your static blog site.]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_thumbnail_id]]></wp:meta_key>
        <wp:meta_value><![CDATA[277]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_awac_hide_widget]]></wp:meta_key>
        <wp:meta_value><![CDATA[]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_wp_old_slug]]></wp:meta_key>
        <wp:meta_value><![CDATA[comments-for-static-blogs]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[ssb_old_counts]]></wp:meta_key>
        <wp:meta_value><![CDATA[a:5:{s:7:"twitter";i:0;s:9:"pinterest";i:0;s:7:"fbshare";i:0;s:6:"reddit";i:0;s:6:"tumblr";i:0;}]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[ssb_total_counts]]></wp:meta_key>
        <wp:meta_value><![CDATA[0]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[ssb_cache_timestamp]]></wp:meta_key>
        <wp:meta_value><![CDATA[442210]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_ssb_hide]]></wp:meta_key>
        <wp:meta_value><![CDATA[false]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_pingme]]></wp:meta_key>
        <wp:meta_value><![CDATA[1]]></wp:meta_value>
        </wp:postmeta>
                            <wp:postmeta>
        <wp:meta_key><![CDATA[_encloseme]]></wp:meta_key>
        <wp:meta_value><![CDATA[1]]></wp:meta_value>
        </wp:postmeta>
                            <wp:comment>
            <wp:comment_id>16</wp:comment_id>
            <wp:comment_author><![CDATA[使用Jekyll和Github页面创建网站/博客的3个步骤 - 无BUG人生]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[]]></wp:comment_author_email>
            <wp:comment_author_url>http://www.mzbug.com/2020/02/24/%e4%bd%bf%e7%94%a8jekyll%e5%92%8cgithub%e9%a1%b5%e9%9d%a2%e5%88%9b%e5%bb%ba%e7%bd%91%e7%ab%99-%e5%8d%9a%e5%ae%a2%e7%9a%843%e4%b8%aa%e6%ad%a5%e9%aa%a4/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[162.158.119.103]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2020-02-24 14:35:02]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2020-02-24 14:35:02]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[[&#8230;] 推荐读物：  为什么Hyvor Talk在静态网站上比其他网站更好？ [&#8230;]]]></wp:comment_content>
            <wp:comment_approved><![CDATA[1]]></wp:comment_approved>
            <wp:comment_type><![CDATA[pingback]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
                    <wp:comment>
            <wp:comment_id>71</wp:comment_id>
            <wp:comment_author><![CDATA[3 Steps to Create a Site/Blog with Jekyll &ndash; Hyvor Talk Blog]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[]]></wp:comment_author_email>
            <wp:comment_author_url>https://talk.hyvor.com/blog/creating-a-static-site-blog-with-jekyll-and-github-pages/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[162.158.79.14]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2020-08-06 06:28:19]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2020-08-06 06:28:19]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[[&#8230;] Recommended Reading: Why Hyvor Talk is a better option for static sites than its alternatives? [&#8230;]]]></wp:comment_content>
            <wp:comment_approved><![CDATA[0]]></wp:comment_approved>
            <wp:comment_type><![CDATA[pingback]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
                    <wp:comment>
            <wp:comment_id>98</wp:comment_id>
            <wp:comment_author><![CDATA[How to Add Comments to Your React-Powered Blog? &ndash; Hyvor Talk Blog]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[]]></wp:comment_author_email>
            <wp:comment_author_url>https://talk.hyvor.com/blog/how-to-add-comments-to-react-powered-blog/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[162.158.63.149]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2020-08-27 06:16:05]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2020-08-27 06:16:05]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[[&#8230;] considering a commenting platform for static sites, the most important factor is speed. Hyvor Talk is fast and [&#8230;]]]></wp:comment_content>
            <wp:comment_approved><![CDATA[0]]></wp:comment_approved>
            <wp:comment_type><![CDATA[pingback]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
                    <wp:comment>
            <wp:comment_id>136</wp:comment_id>
            <wp:comment_author><![CDATA[The Rise Of Static Sites - We share everything that is online]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[]]></wp:comment_author_email>
            <wp:comment_author_url>https://knnit.com/the-rise-of-static-sites/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[172.69.55.93]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2020-10-10 05:34:37]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2020-10-10 05:34:37]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[[&#8230;] or Drupal work with this static-first approach. This however had changed. The fact here is that Adding Comments to Static Sites is something that applicable at this [&#8230;]]]></wp:comment_content>
            <wp:comment_approved><![CDATA[0]]></wp:comment_approved>
            <wp:comment_type><![CDATA[pingback]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
                    <wp:comment>
            <wp:comment_id>180</wp:comment_id>
            <wp:comment_author><![CDATA[How to Start a Blog with Ucraft &ndash; Hyvor Talk Blog]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[]]></wp:comment_author_email>
            <wp:comment_author_url>https://talk.hyvor.com/blog/how-to-start-a-blog-with-ucraft/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[108.162.219.209]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2021-02-21 21:30:56]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2021-02-21 21:30:56]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[[&#8230;] considering a&nbsp;commenting platform for static sites, the most important factor is&nbsp;speed. Hyvor Talk is fast and [&#8230;]]]></wp:comment_content>
            <wp:comment_approved><![CDATA[0]]></wp:comment_approved>
            <wp:comment_type><![CDATA[pingback]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
                    <wp:comment>
            <wp:comment_id>213</wp:comment_id>
            <wp:comment_author><![CDATA[twitter takipçi hilesi]]></wp:comment_author>
            <wp:comment_author_email><![CDATA[asd45a546sd654a@gmail.com]]></wp:comment_author_email>
            <wp:comment_author_url>https://twitpanda.com/</wp:comment_author_url>
            <wp:comment_author_IP><![CDATA[10.114.0.4]]></wp:comment_author_IP>
            <wp:comment_date><![CDATA[2021-12-12 00:45:52]]></wp:comment_date>
            <wp:comment_date_gmt><![CDATA[2021-12-12 00:45:52]]></wp:comment_date_gmt>
            <wp:comment_content><![CDATA[Let's give a hug as those who see your profile ????]]></wp:comment_content>
            <wp:comment_approved><![CDATA[0]]></wp:comment_approved>
            <wp:comment_type><![CDATA[comment]]></wp:comment_type>
            <wp:comment_parent>0</wp:comment_parent>
            <wp:comment_user_id>0</wp:comment_user_id>
                            </wp:comment>
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
expect($postVariant->status->value)->toBeIn(['draft','published']);
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


test('Testing the importer', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));
    
    $parser = new WordpressParser($file);
    
    $repo = $parser->parse();

    $blog = Blog::findOrFail(4);
    
    $import = new Import();

    $importer = new Importer($repo,$blog,$import);

    $importer->import();

})->group('importer_test');


