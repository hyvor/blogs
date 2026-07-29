<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class TagTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_tag_page(): void
    {
        $content = <<<TXT
        Meta Title: {{ _meta.title }}
        Meta Description: {{ _meta.description }}
        Meta URL: {{ _meta.url }}
        Tag Slug: {{ _tag.slug }}
        Posts count: {{ _posts|length }}
        Post Title: {{ _posts[0].title }}
        TXT;

        $expected = <<<TXT
        Meta Title: My Tag
        Meta Description: Tag page for my-tag
        Meta URL: https://example.hyvorblogs.io/tag/my-tag
        Tag Slug: my-tag
        Posts count: 1
        Post Title: My Post 1
        TXT;

        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'example',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
        ]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'tag.twig',
            'content' => $content,
        ]);
        $tag = TagFactory::createOne(['blog' => $blog, 'slug' => 'my-tag', 'is_private' => false]);
        TagVariantFactory::createOne([
            'tag' => $tag,
            'language' => $blog->getLanguages()[0],
            'name' => 'My Tag',
            'description' => 'Tag page for my-tag',
        ]);

        $post1 = PostFactory::createPublishedOneForWithVariants($blog, variantAttributes: [
            'title' => 'My Post 1',
        ]);
        $post1->getTags()->add($tag);

        $post2 = PostFactory::createPublishedOneForWithVariants($blog);

        $response = $this->pathMatcher()->match($blog, '/tag/my-tag');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($expected, $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_does_not_match_private_tag(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'tag.twig',
            'content' => 'I am a tag',
        ]);
        TagFactory::createOne(['blog' => $blog, 'slug' => 'private-tag', 'is_private' => true]);

        $response = $this->pathMatcher()->match($blog, '/tag/private-tag');

        $this->assertSame(404, $response->status);
    }
}
