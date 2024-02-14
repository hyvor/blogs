<?php declare(strict_types=1);

namespace App\Domains\Export\WordPress;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\User;
use XMLWriter;

class WxrExporter
{

    private XMLWriter $writer;

    public function __construct(
        private Blog $blog,
        private Language $language,
        private string $filePath,
    )
    {
        $this->writer = new XMLWriter();
        $this->writer->openUri($this->filePath);
    }

    public function write() : void
    {

        $this->writer->startDocument('1.0', 'UTF-8');

        $this->writer->startElement('rss');
        $this->writer->writeAttribute('version', '2.0');
        $this->writer->writeAttribute('xmlns:excerpt', 'http://wordpress.org/export/1.2/excerpt/');
        $this->writer->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $this->writer->writeAttribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
        $this->writer->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $this->writer->writeAttribute('xmlns:wp', 'http://wordpress.org/export/1.2/');

        $this->writeBlog();

        $this->writer->endDocument();
    }

    private function writeBlog()
    {

        $this->writer->startElement('channel');

        $blogVariant = $this->blog->variants->where('language_id', $this->language->id)->first();
        $blogUrl = PermalinkRepository::getBaseUrl($this->blog);

        $this->writer->writeElement('title', $blogVariant?->name);
        $this->writer->writeElement('link', $blogUrl);
        $this->writer->writeElement('description', $blogVariant?->description);
        $this->writer->writeElement('pubDate', gmdate( 'D, d M Y H:i:s +0000' ));
        $this->writer->writeElement('language', $this->language->code);
        $this->writer->writeElement('wp:wxr_version', '1.2');
        $this->writer->writeElement('wp:base_site_url', $blogUrl);
        $this->writer->writeElement('wp:base_blog_url', $blogUrl);

        $this->writeAuthors();
        $this->writeCategories();
        $this->writePosts();

        $this->writer->endElement();

    }

    // HB users with posts_count > 0 are WP authors
    private function writeAuthors()
    {

        User::where('posts_count', '>', 0)
            ->orderBy('id')
            ->where('blog_id', $this->blog->id)
            ->get()
            ->each(function($user) {

            $variant = $user->variants->where('language_id', $this->language->id)->first();

            $this->writer->startElement('wp:author');

            $this->writer->writeElement('wp:author_id', (string) $user->id);
            $this->writer->writeElement('wp:author_login', $user->slug);
            $this->writer->writeElement('wp:author_email', $user->email);

            // get first, last name
            $name = $variant?->name ?? '';
            $namePars = explode(' ', $name);
            $firstName = $namePars[0] ?? '';
            $lastName = $namePars[1] ?? '';

            $this->writer->writeElement('wp:author_display_name', $name);
            $this->writer->writeElement('wp:author_first_name', $firstName);
            $this->writer->writeElement('wp:author_last_name', $lastName);

            $this->writer->endElement();
        });

    }

    // HB tags are WP categories
    private function writeCategories()
    {

        Tag::where('blog_id', $this->blog->id)
            ->orderBy('id')
            ->get()
            ->each(function($tag) {

            $this->writer->startElement('wp:category');

            $this->writer->writeElement('wp:term_id', (string) $tag->id);
            $this->writer->writeElement('wp:category_nicename', $tag->slug);
            $this->writer->writeElement('wp:category_parent', '');

            $variant = $tag->variants->where('language_id', $this->language->id)->first();
            $this->writer->writeElement('wp:cat_name', $variant?->name ?? '');
            $this->writer->writeElement('wp:category_description', $variant?->description ?? '');

            $this->writer->endElement();
        });

    }

    private function writePosts()
    {

        Post::where('blog_id', $this->blog->id)
            ->orderBy('id')
            ->join('post_variants', function ($join) {
                $join->on('post_variants.post_id', '=', 'posts.id');
                $join->where('post_variants.language_id', '=', $this->language->id);
            })
            ->select('posts.*')
            ->get()
            ->each(function($post) {

                $variant = $post->variants->where('language_id', $this->language->id)->first();

                $primaryAuthor = $post->authors->first();
                $postUrl = PermalinkRepository::getPostPermalink($post, $this->blog, $this->language);

                $this->writer->startElement('item');

                $this->writeCdataElement('title', $variant?->title);
                $this->writer->writeElement('link', $postUrl);
                $this->writer->writeElement('pubDate', $post->published_at->format('D, d M Y H:i:s +0000'));
                $this->writeCdataElement('dc:creator', $primaryAuthor->slug);
                $this->writer->writeElement('guid', $postUrl);
                $this->writer->writeElement('description', '');
                $this->writeCdataElement('content:encoded', $this->getPostContentHtml($post, $variant));
                $this->writeCdataElement('excerpt:encoded', $variant?->description);
                $this->writer->writeElement('wp:post_id', (string) $post->id);
                $this->writer->writeElement('wp:post_date', $post->created_at->format('Y-m-d H:i:s'));
                $this->writer->writeElement('wp:post_date_gmt', $post->created_at->format('Y-m-d H:i:s'));
                $this->writer->writeElement('wp:comment_status', 'open');
                $this->writer->writeElement('wp:ping_status', 'open');
                $this->writer->writeElement('wp:post_name', $post->slug);
                $this->writer->writeElement('wp:status', 'publish');
                $this->writer->writeElement('wp:post_parent', '0');
                $this->writer->writeElement('wp:menu_order', '0');
                $this->writer->writeElement('wp:post_type', 'post');
                $this->writer->writeElement('wp:post_password', '');
                $this->writer->writeElement('wp:is_sticky', '0');
                $this->writer->writeElement('wp:postmeta', '');

            });

    }

    private function writeCdataElement(string $name, ?string $value)
    {
        $this->writer->startElement($name);
        $this->writer->writeCdata($value ?? '');
        $this->writer->endElement();
    }


    private function getPostContentHtml(Post $post, PostVariant $variant)
    {
        return $variant->content_html ?? '';
    }

}