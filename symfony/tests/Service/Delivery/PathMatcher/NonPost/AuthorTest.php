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
use App\Tests\Factory\ThemeFileFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class AuthorTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_author_page(): void
    {

        $content = <<<TXT
        Meta Title: {{ _meta.title }}
        Meta Description: {{ _meta.description }}
        Meta URL: {{ _meta.url }}
        Author Slug: {{ _author.slug }}
        Posts count: {{ _posts|length }}
        Post Title: {{ _posts[0].title }}
        TXT;

        $expected = <<<TXT
        Meta Title: My Author
        Meta Description: Author page for my-author
        Meta URL: https://example.hyvorblogs.io/author/my-author
        Author Slug: my-author
        Posts count: 1
        Post Title: My Post 1
        TXT;

        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'example',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
        ]);
        ThemeFileFactory::createTemplateTwig($blog, 'author.twig', $content);
        $user = UserFactory::createOne(['blog' => $blog, 'slug' => 'my-author']);
        UserVariantFactory::createOne([
            'user' => $user,
            'language' => $blog->getLanguages()[0],
            'name' => 'My Author',
            'bio' => 'Author page for my-author',
        ]);

        $post1 = PostFactory::createPublishedOneForWithVariants($blog, variantAttributes: [
            'title' => 'My Post 1',
        ]);
        $post1->getAuthors()->add($user);
        $post2 = PostFactory::createPublishedOneForWithVariants($blog);

        $response = $this->pathMatcher()->match($blog, '/author/my-author');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($expected, $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }
}
