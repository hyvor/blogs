<?php

namespace Tests\Unit\Import\Parsers;

use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Importer;
use App\Models\Blog;
use App\Models\Import;

test('testing users and users varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/">

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
$repo = $parser->parse(true);

$user = $repo->userModels[0];

expect($user->id)->toBe(1);
expect($user->blog_id)->toBe(1);
expect($user->email)->toBe('supun@hyvor.com');

$userVariant = $repo->userVariantModels[0];

expect($userVariant->id)->toBe(1);
expect($userVariant->name)->toBe('Supun kavinda');

})->group('parser');


test('testing tags and tags varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/">

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
$repo = $parser->parse(false,true);

$tag = $repo->tagModels[0];

expect($tag->id)->toBe(50);
expect($tag->blog_id)->toBe(1);

$tagVariant = $repo->tagVariantModels[0];

expect($tagVariant->id)->toBe(50);
expect($tagVariant->name)->toBe('Announcements');

})->group('parser');


test('testing post and post varient',function(){

$str = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/">

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
        <description></description>
        <content:encoded><![CDATA[<!-- wp:paragraph -->
<p><a href="https://www.staticgen.com/">Static Site Generators</a> like Jekyll, Gatsby, and Hugo are <a href="https://hackernoon.com/rise-of-static-site-generators-and-the-destiny-of-cms-e2b8ff0d5fcc">on the rise</a> for various reasons such as speed, version control, data protection, and security. As a result,  many bloggers, especially ones in the technical field, are gradually <a href="https://palant.de/2019/04/04/switching-my-blog-to-a-static-site-generator/">switching</a> to Static Site Generators for their blog sites. Even they use static sites, they usually expect one dynamic thing: comments. So, in this article, I'll be explaining how to add comments to your static site.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":277,"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://talk.hyvor.com/blog/wp-content/uploads/2020/01/photo-1516321497487-e288fb19713f.jpg" alt="Adding comments to a static blog sites" class="wp-image-277"/><figcaption>Let's add comments (<a href="https://unsplash.com/photos/2FPjlAyMQTA">Unsplash</a>)</figcaption></figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2>When to add Comments to a Static Blog?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Jeff Atwood at CodingHorror asserts that <a href="https://blog.codinghorror.com/a-blog-without-comments-is-not-a-blog/">a blog without comments isn't a blog</a>. However, in my opinion, <strong>all static blogs don't need comments</strong>. It's completely the preference of the blogger!</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Like to have more user engagements?</li><li>Need to know what visitors think about your blog posts?</li><li>Want to let visitors contribute to your content?</li><li>Love to increase on-page SEO using the content generated by users?</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>If the answer to any of those questions is yes, you can add a commenting system for your website.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Unlike WordPress, static sites don't come with in-built <a href="http://talk.hyvor.com">commenting system</a>s. Therefore, you'll need to choose a third-party commenting system for your static site comments. In this article, I'll be using <a href="https://talk.hyvor.com">Hyvor Talk</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why Hyvor Talk?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a href="https://talk.hyvor.com">Hyvor Talk</a> is a third-party commenting system.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>First, you can install it with ease - it's just a copy &amp; paste task.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Next, it has an endless list of features making it a fully-functional commenting platform. Here are some hand-picked features that I think will be useful for you as a static blog enthusiast. </p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Easy installation</li><li>Full customizability</li></ul>
<!-- /wp:list -->

<!-- wp:image {"align":"center","id":286,"sizeSlug":"large"} -->
<div class="wp-block-image"><figure class="aligncenter size-large"><img src="https://talk.hyvor.com/blog/wp-content/uploads/2020/01/image-10.png" alt="Changing appearance of commenting platform on static sites" class="wp-image-286"/></figure></div>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>Easy and powerful moderation - Hyvor Talk has a powerful AJAX-based moderation console and <a href="https://talk.hyvor.com/docs/moderating-comments">moderation features</a>.</li><li>Previously used other commenting plugins? You can import <a href="https://talk.hyvor.com/docs/import-intro">your comments</a>.</li></ul>
<!-- /wp:list -->

<!-- wp:image {"align":"center","id":288,"sizeSlug":"large"} -->
<div class="wp-block-image"><figure class="aligncenter size-large"><img src="https://talk.hyvor.com/blog/wp-content/uploads/2020/01/image-11.png" alt="Importing Comments from previous commenting platforms" class="wp-image-288"/></figure></div>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>No ads are shown.</li><li>Fast Loading. Lazy loading supported by default.</li></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>How To Add Comments To A Static Blog?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Adding comments to your static blog sites is straightforward with Hyvor Talk and depends on the static generator you use.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>As most of the static generators use HTML-based template systems, you can easily copy &amp; paste the manual code (HTML + JS) into your template. The best place is after the blog post content and before the footer.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you have such templates on your static site:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>First, you'll need to log in to the <a href="https://talk.hyvor.com/console">console</a>.</li><li>Then, get your installation code.</li><li>Add it to your template in the correct place.</li></ul>
<!-- /wp:list -->

<!-- wp:list -->
<ul><li><strong>On official docs</strong>: <a href="https://talk.hyvor.com/docs/install?section=manual-installation">How to manually install Hyvor Talk?</a></li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>If you have a react-powered blog, you can use our <a href="https://github.com/HyvorTalk/hyvor-talk-react">React plugin</a>. If you need a more detailed guide on installing Hyvor Talk on a react-powered blog see this guide: <a href="https://talk.hyvor.com/blog/how-to-add-comments-to-react-powered-blog/">How to add comments to react-powered blog?</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Here are some other tutorials on adding Hyvor Talk comments to some famous static site generators.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Hexo</li><li>Jekyll</li><li>Gatsby</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>If you have any questions or problems regarding adding comments to your static site, please comment below.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>How fast is Hyvor Talk?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Hyvor Talk makes a minimal amount of HTTP requests. All the Javascript files are bundled into one file. Additionally, Hyvor Talk serves <a href="https://developers.google.com/speed/docs/insights/MinifyResources">minified</a> and <a href="https://en.wikipedia.org/wiki/Gzip">GZIP Compressed</a> Javascript and CSS files.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Hyvor Talk uses inline SVG images for reaction emojis which cuts the cost of HTTP requests and makes the loading much faster. (Oh, I forgot. Hyvor Talk comes with a reaction plugin too!)</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":284,"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://talk.hyvor.com/blog/wp-content/uploads/2020/01/image-9.png" alt="Reactions for Static Sites" class="wp-image-284"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Thankfully, Hyvor Talk doesn't place any third-party scripts (ads or analytics) on your website which makes Hyvor Talk much faster than its alternatives. It makes Hyvor Talk a suitable solution for static site comments.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Here's the best thing: Hyvor Talk supports Lazy Loading by default. See <a href="https://talk.hyvor.com/documentation/installation/loading-modes">Loading Modes</a> for more details. There's zero impact on your website performance if you use Lazy Loading.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>SEO with Static Site Comments</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>User-generated content is an easy and effective method to improve your site's SEO. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>One of the most asked questions is "does Google index Hyvor Talk comments?"</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The answer is "<strong>Yes</strong>".</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Google will index the comments and those fresh content will help you rank better on SERP.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>How to Prevent Spam?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Most blogs, usually large ones, face the problem of spam. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>When using a CMS that comes with a native comment system, like <a href="https://talk.hyvor.com/blog/prevent-comment-spam-on-wordpress/">WordPress, preventing spam</a> is a hard task. That's why most of the famous sites use a third-party commenting platform on their sites. However, Hyvor Talk will help you to make spam comments minimal.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Hyvor Talk has an in-built spam detector that works on multiple factors. All the guest comments are protected with Google Recaptcha to prevent bot spam.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>You can also set custom <a href="https://talk.hyvor.com/documentation/moderation/moderation-rules">automated moderation rules</a> to moderate the comments in the way you need.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Conclusion</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>In this article, we discussed when to add comments to static sites, why it's important, and why Hyvor Talk is the best option. I hope you enjoyed the article. If you have any questions please comment below. ✌</p>
<!-- /wp:paragraph -->]]></content:encoded>
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
$repo = $parser->parse(true,true,true);

$post = $repo->postModels[0];
expect($post->id)->toBe(273);
expect($post->blog_id)->toBe(1);

/*$tagVariant = $repo->tagVariantModels[0];

expect($tagVariant->id)->toBe(502);
expect($tagVariant->name)->toBe('Announcements');*/

})->group('parser');


it('Returns the users models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->userModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->role)->toBeObject();
        expect($value->status)->toBeObject();
        expect($value->slug)->toBeString();

        expect($repo->userModels[$key])->toBeObject();
    }


})->group('modelsTest');

it('Returns the tags models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->tagModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->slug)->toBeString();
        expect($value->posts_count)->toBeInt();
        
        expect($repo->tagModels[$key])->toBeObject();
    }


})->group('modelsTest');


it('Returns the post models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->postModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->slug)->toBeString();
        expect($value->is_page)->toBeBool();
        expect($value->is_featured)->toBeInt();
        
        expect($repo->postModels[$key])->toBeObject();
    }


})->group('modelsTest');


it('parsers the language', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    
    $repo = $parser->parse();

    foreach ($repo->lang as $lang) {
        $language = $lang['language'];
        $languageCode = $lang['languageCode'];
    }

    $this->assertEquals('English', $language);

})->group('parserTest');

it('parsers the authors', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->authors as $key=>$value){
        
        expect($value['id'])->toBeInt();
        expect($value['name'])->toBeString();
        expect($value['email'])->toBeString();
        expect($value['status'])->toBeString();
        expect($value['role'])->toBeString();
        expect($value['slug'])->toBeString();
        
    }

    expect($repo->authors)->toBeArray();

})->group('parserTest');


it('parsers the tags', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->tags as $tag) {
        expect($tag['slug'])->toBeString();
        expect($tag['postsCount'])->toBeInt();
    }

    expect($repo->tags)->toBeArray();

})->group('parserTest');

it('parsers the posts', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->posts as $post) {
        
        expect($post['slug'])->toBeString();
        expect($post['isPage'])->toBeBool();
        expect($post['title'])->toBeString();
        expect($post['description'])->toBeString();
        expect($post['status'])->toBeString();
        expect($post['content'])->toBeString();
    }

    expect($repo->posts)->toBeArray();

})->group('parserTest');



test('Testing the importer', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));
    
    $parser = new WordpressParser($file);
    
    $repo = $parser->parse();

    $blog = Blog::findOrFail(4);
    
    $import = new Import();

    $importer = new Importer($repo,$blog,$import);

    $importer->import();

})->group('importer_test');


