<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Meta\BlogMeta;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class BlogTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_sets_blog_variable(): void
    {
        $content = <<<TXT
        Blog subdomain: {{ _blog.subdomain }}
        Blog URL: {{ _blog.url }}
        Blog base URL: {{ _blog.base_url }}
        Twitter URL: {{ _blog.social.twitter }}
        Languages count: {{ _blog.languages|length }}
        Language code: {{ _blog.languages[0].code }}
        TXT;

        $expectedContent = <<<TXT
        Blog subdomain: my-blog
        Blog URL: https://my-blog.hyvorblogs.io
        Blog base URL: https://my-blog.hyvorblogs.io
        Twitter URL: https://x.com/HyvorBlogs
        Languages count: 1
        Language code: en
        TXT;

        $meta = new BlogMeta();
        $meta->social_twitter = 'https://x.com/HyvorBlogs';
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'my-blog',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
            'meta' => $meta,
        ]);
        ThemeFileFactory::createIndexTwig($blog, $content);

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame($expectedContent, $response->content);
    }

}
