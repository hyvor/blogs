<?php

namespace App\Domains\Export;

use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Route\PermalinkRepository;

/**
 * To export blog content in WordPress format
 *
 * Trying to be exact as this export file in WordPress
 * https://github.com/WordPress/WordPress/blob/c569c157f0400344786ce94744c49afce1566e77/wp-admin/includes/export.php
 */
class WordpressExporter implements ExporterInterface
{
    use ExporterTrait;

    /**
     * In WordPress, media are also returned added as posts (type = attachment) to the export file.
     * However, there's no real use of doing this as only meta data of media are added. We only add posts and pages
     *
     * @return string WXR file
     */
    public function getFile()
    {
        $whrVersion = '1.2';

        $blog = $this->getBlog();
        $blogUrl = PermalinkRepository::getBlogPermalink($blog);
        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog)->code;
        $pubDate = now()->format('D, d M Y H:i:s +0000');

        $authors = $this->loopAuthors(function ($author, $ret) {
            $authorEmail = self::CDATA($author->email);
            $authorName = self::CDATA($author->name);
            $authorNameSplit = explode(' ', $author, 2);
            $authorFirstName = self::CDATA($authorNameSplit[0]);
            $authorLastName = self::CDATA($authorNameSplit[1] ?? '');

            $ret .= <<<XML

                <wp:author>
                    <wp:author_id>{$author->id}</wp:author_id>
                    <wp:author_login>$authorEmail</wp:author_login>
                    <wp:author_email>$authorEmail</wp:author_email>
                    <wp:author_display_name>$authorName</wp:author_display_name>
                    <wp:author_first_name>$authorFirstName</wp:author_first_name>
                    <wp:author_last_name>$authorLastName</wp:author_last_name>
                </wp:author>

            XML;

            return $ret;
        }, '');

        $tags = $this->loopTags(function ($tag, $ret) {
            $tagSlug = self::CDATA($tag->slug);
            $tagParent = self::CDATA('');
            $tagName = self::CDATA($tag->name);

            $tagDescription = $tag->description ?
                '<wp:category_description>'.self::CDATA($tag->description).'</wp:category_description>' : '';

            $ret .= <<<XML

                <wp:category>
                    <wp:term_id>{$tag->id}</wp:term_id>
                    <wp:category_nicename>{$tagSlug}</wp:category_nicename>
                    <wp:category_parent>{$tagParent}</wp:category_parent>
                    <wp:cat_name>{$tagName}</wp:cat_name>
                    $tagDescription
                </wp:category>

            XML;

            return $ret;
        }, '');

        $posts = $this->loopPosts(function ($post, $ret) {
            $title = self::CDATA($post->title);
            $link = PermalinkRepository::getPostPermalink($post, $this->blog);

            $publishedAt = $post->published_at ?: $post->created_at;
            $pubDate = $publishedAt->format('D, d M Y H:i:s +0000');
            $postDate = self::CDATA($publishedAt->toDateTimeString());
            $postUpdatedDate = self::CDATA($post->updated_at->toDateTimeString());

            $primaryAuthor = PostTagAuthorRepository::getPrimaryAuthor($post);
            $primaryAuthorEmail = self::CDATA($primaryAuthor?->email ?? '');
            $content = $post->content;
            $excerpt = self::CDATA($post->description);
            $isFeatured = (int) $post->is_featured;
            $commentStatus = self::CDATA('open');
            $pingStatus = self::CDATA('open');

            $postName = self::CDATA($post->slug);

            $status = match ($post->status) {
                'published' => 'publish',
                'draft' => 'draft',
                'scheduled' => 'scheduled',
            };
            $status = self::CDATA($status);

            $postType = self::CDATA($post->is_page ? 'page' : 'post');

            $cdata = self::CDATA('');

            $tags = '';
            foreach ($post->tags as $tag) {
                $tagName = self::CDATA($tag->name);
                $tags .= <<<XML
                    <category domain="category" nicename="{$tag->slug}">$tagName</category>
                XML;
            }

            /**
             * TODO: Add comments here (from Hyvor Talk)
             */
            $comments = '';

            $ret .= <<<XML

                <item>
                    <title>$title</title>
                    <link>$link</link>
                    <pubDate>$pubDate</pubDate>
                    <dc:creator>$primaryAuthorEmail</dc:creator>
                    <guid isPermaLink="false">$link</guid>
                    <description></description>
                    <content:encoded>$content</content:encoded>
                    <excerpt:encoded>$excerpt</excerpt:encoded>
                    <wp:post_id>$post->id</wp:post_id>
                    <wp:post_date>$postDate</wp:post_date>
                    <wp:post_date_gmt>$postDate</wp:post_date_gmt>
                    <wp:post_modified>$postUpdatedDate</wp:post_modified>
                    <wp:post_modified_gmt>$postUpdatedDate</wp:post_modified_gmt>
                    <wp:comment_status>$commentStatus</wp:comment_status>
                    <wp:ping_status>$pingStatus</wp:ping_status>
                    <wp:post_name>$postName</wp:post_name>
                    <wp:status>$status</wp:status>
                    <wp:post_parent>0</wp:post_parent>
                    <wp:menu_order>0</wp:menu_order>
                    <wp:post_type>$postType</wp:post_type>
                    <wp:post_password>$cdata</wp:post_password>
                    <wp:is_sticky>$isFeatured</wp:is_sticky>

                    $tags

                    $comments
                
                </item>

            XML;

            return $ret;
        }, '');

        return <<<XML

            <rss version="2.0"
                xmlns:excerpt="http://wordpress.org/export/$whrVersion/excerpt/"
                xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:wp="http://wordpress.org/export/$whrVersion/"
            >

                <channel>

                    <title>$blog->name</title>
                    <link>$blogUrl</link>
                    <description>$blog->description</description>
                    <pubDate>$pubDate</pubDate>
                    <language>$primaryLanguage</language>
                    <wp:wxr_version>$whrVersion</wp:wxr_version>
                    <wp:base_site_url>$blogUrl</wp:base_site_url>
                    <wp:base_blog_url>$blogUrl</wp:base_blog_url>

                    $authors
                    $tags
                    $posts

                </channel>

            </rss>

        XML;
    }

    public static function CDATA($str)
    {
        if (mb_detect_encoding($str, ['UTF-8']) !== 'UTF-8') {
            $str = utf8_encode($str);
        }
        // $str = ent2ncr(esc_html($str));
        $str = '<![CDATA['.str_replace(']]>', ']]]]><![CDATA[>', $str).']]>';

        return $str;
    }
}
