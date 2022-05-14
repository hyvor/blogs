<?php

namespace Tests\Unit\Import\Parsers;

use App\Models\Import;
use App\Models\Blog;
use App\Models\User;
use App\Models\UserVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PostTag;
use App\Models\PostAuthor;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;



use App\Domains\Import\Repository;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

// first need dummy export data in xml ---done
// second need to pass the export data to the repository ---done
// then need to pass the passed data to the importer and then check the whether its saving in the database.

$content = <<<XML
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss>
            <channel>
                <title>Hyvor Blogs Blog</title>
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

// $repo = new Repository();
// $data = new Crawler($content);


// test('pass tag data to repository.', function () use ($repo, $data){

//     $tagId = 1;
//     $tagName = 'test';
//     $slug = Str::slug($tagName.rand());
//     $created_at = date("Y/m/d h:i:s");
//     $updated_at = date("Y/m/d h:i:s");

//     $repo->tag(
//         id: $tagId,
//         name: $tagName,
//         slug: $slug,
//         created_at: $created_at,
//         updated_at: $updated_at,
//     );
    
//     $this->assertJson(200);
// });