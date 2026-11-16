<?php

namespace App\Tests\Api\Console\Org;

use App\Api\Console\ControllerOrg\BlogController;
use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeCreationType;
use App\Entity\Enum\UserRole;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Route;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Blog\BlogCreator;
use App\Service\Blog\Event\BlogCreatedEvent;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFactory;
use App\Tests\Factory\ThemeVersionFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Deployment;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogCreator::class)]
#[CoversClass(BlogCreatedEvent::class)]
class CreateBlogTest extends ApiTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // themes are needed to create a blog
        foreach (['hello', 'blank'] as $themeName) {
            $theme = ThemeFactory::createOne(['name' => $themeName, 'type' => ThemeCreationType::ORIGINAL]);
            ThemeVersionFactory::createOne([
                'theme' => $theme,
                'version' => '1.0.0',
                'zip' => $this->makeZip(['config.yaml' => "test: true\n"]),
            ]);
        }
    }

    /** @param array<string, string> $files */
    private function makeZip(array $files): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'zip-fixture-');
        $zip = new \ZipArchive();
        $zip->open((string) $tmp, \ZipArchive::OVERWRITE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        $content = (string) file_get_contents((string) $tmp);
        unlink((string) $tmp);
        return $content;
    }


    private function enableOnPremise(): void
    {
        $this->setEnvVar('DEPLOYMENT', Deployment::ON_PREM->value);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function create(array $data, bool $hasPaymentMethod = true): Response
    {
        $user = AuthFake::generateUser(['id' => 501]);
        $org = new AuthUserOrganization(1, 'Test Org', 'admin');
        return $this->consoleOrgApi('POST', '/blog', $data, user: $user,
            organization: $org,
            organizationsDatabase: [['id' => 1, 'has_payment_method' => $hasPaymentMethod]],
        );
    }

    public function test_requires_name(): void
    {
        $this->create([]);
        $this->assertResponseFailed(422, 'name: This value should not be blank.');
    }

    public function test_requires_subdomain_unless_dev(): void
    {
        $this->create(['name' => 'Testing']);
        $this->assertResponseFailed(422, 'subdomain: This value should not be blank.');
    }

    public function test_rejects_reserved_subdomain(): void
    {
        $this->create(['name' => 'Testing', 'subdomain' => 'new']);
        $this->assertResponseFailed(422, 'subdomain is reserved');
    }

    public function test_does_not_allow_taken_subdomain(): void
    {
        BlogFactory::createOne(['subdomain' => 'taken-subdomain']);

        $this->create(['name' => 'Second', 'subdomain' => 'taken-subdomain']);
        $this->assertResponseFailed(422, 'subdomain is already taken');
    }

    public function test_creates_default_blog(): void
    {
        $this->enableOnPremise();
        $this->create(['name' => 'My Blog', 'subdomain' => 'new-blog']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        /** @var array<string, mixed> $blogJson */
        $blogJson = $json['blog'] ?? [];
        $this->assertSame('new-blog', $blogJson['subdomain']);
        $this->assertSame('default', $blogJson['type']);
        $this->assertSame('admin', $blogJson['role']);

        $blogId = $blogJson['id'];
        $blog = $this->getEm()->getRepository(Blog::class)->find($blogId);
        $this->assertInstanceOf(Blog::class, $blog);
        $this->assertSame(BlogType::DEFAULT, $blog->getType());
        $this->assertSame('127.0.0.1', $blog->getIp());
        $this->assertSame(501, $blog->getHyvorUserId());
        $this->assertSame(1, $blog->getOrganizationId());
        $counts = $blog->getCounts();
        $this->assertIsArray($counts);
        $this->assertArrayHasKey('posts', $counts);
        $this->assertSame(2, $counts['posts']);

        // BlogVariant filler
        $variants = $this->getEm()->getRepository(BlogVariant::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $variants);
        $this->assertSame('My Blog', $variants[0]->getName());

        // LanguageFiller: only English for a default blog
        $languages = $this->getEm()->getRepository(Language::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $languages);
        $this->assertSame('en', $languages[0]->getCode());
        $this->assertTrue($languages[0]->isPrimary());

        // UserFiller: the creator becomes the admin (owner)
        $users = $this->getEm()->getRepository(User::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $users);
        $this->assertSame(UserRole::ADMIN, $users[0]->getRole());
        $this->assertSame(501, $users[0]->getHyvorUserId());

        // TagFiller: one "Welcome" tag
        $tags = $this->getEm()->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $tags);
        $this->assertSame('welcome', $tags[0]->getSlug());

        // RouteFiller: the 5 standard routes
        $routes = $this->getEm()->getRepository(Route::class)->findBy(['blog' => $blog]);
        $this->assertCount(5, $routes);

        // NavigationFiller: 3 default nav items
        $navigations = $this->getEm()->getRepository(Navigation::class)->findBy(['blog' => $blog]);
        $this->assertCount(3, $navigations);

        // PostFiller: 2 posts + 3 pages
        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(5, $posts);
        $contentStylesPostVariant = $this->getEm()->getRepository(PostVariant::class)->findOneBy(['slug' => 'content-style']);
        $this->assertNotNull($contentStylesPostVariant);
        $this->assertStringContainsString(
            'learn how to add these blocks',
            (string) $contentStylesPostVariant->getContent()
        );
        $this->assertStringContainsString(
            'https://hyvor.com/blogs/docs/writing',
            (string) $contentStylesPostVariant->getContentHtml()
        );
        $this->assertStringContainsString(
            'Headings are used to write subtitles in posts',
            (string) $contentStylesPostVariant->getContentText()
        );

        // ThemeFiller: "hello" theme copied
        $themeFilesService = $this->getService(ThemeFilesService::class);
        $files = $themeFilesService->getAllFilesOfBlog($blog);
        $this->assertNotEmpty($files);

        $event = $this->getEd()->getFirstEvent(BlogCreatedEvent::class);
        $this->assertSame($blog->getId(), $event->blog->getId());
    }

    public function test_creates_dev_blog(): void
    {
        $this->enableOnPremise();
        $this->create(['name' => 'Dev Blog', 'is_dev' => true]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $blogJson = $json['blog'];
        $this->assertIsArray($blogJson);
        $this->assertSame('dev', $blogJson['type']);
        /** @var string $subdomain */
        $subdomain = $blogJson['subdomain'];
        $this->assertMatchesRegularExpression('/^dev-[0-9a-f-]{36}$/i', $subdomain);

        $blog = $this->getEm()->getRepository(Blog::class)->find($blogJson['id']);
        $this->assertInstanceOf(Blog::class, $blog);

        // dev blogs get 3 languages: en, fr, ar
        $languages = $this->getEm()->getRepository(Language::class)->findBy(['blog' => $blog]);
        $this->assertCount(3, $languages);

        // 1 admin + 5 guest users
        $users = $this->getEm()->getRepository(User::class)->findBy(['blog' => $blog]);
        $this->assertCount(6, $users);

        // "welcome" + 5 random tags
        $tags = $this->getEm()->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(6, $tags);

        // the 5 base posts + 15 random posts
        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(20, $posts);

        // "blank" theme copied
        $themeFilesService = $this->getService(ThemeFilesService::class);
        $files = $themeFilesService->getAllFilesOfBlog($blog);
        $this->assertNotEmpty($files);
    }

    public function test_does_not_enforce_blog_limit_on_prem(): void
    {
        BlogFactory::createOne(['organization_id' => 1, 'type' => BlogType::DEFAULT]);
        $this->enableOnPremise();

        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiCost: 0,
            seoAnalysis: false,
            linkAnalysis: false,
            blogs: 1,
            noBranding: false,
        );
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $personalLicense)],
        );

        // deployment defaults to on-prem in tests, so the license limit is not enforced
        $this->create(['name' => 'Second', 'subdomain' => 'blog-limit-on-prem']);
        $this->assertResponseIsSuccessful();
    }

    public function test_requires_payment_method_on_cloud(): void
    {
        $this->create(['name' => 'No Card', 'subdomain' => 'no-card-cloud'], hasPaymentMethod: false);

        $this->assertResponseFailed(
            422,
            'A payment method is required to create a blog. Please add a card and try again.',
        );
    }

    public function test_allows_blog_creation_with_payment_method_on_cloud(): void
    {

        $this->create(['name' => 'With Card', 'subdomain' => 'with-card-cloud']);
        $this->assertResponseIsSuccessful();
    }

    public function test_does_not_require_payment_method_on_prem(): void
    {
        $this->enableOnPremise();

        $this->create(['name' => 'On Prem', 'subdomain' => 'no-card-on-prem'], hasPaymentMethod: false);
        $this->assertResponseIsSuccessful();
    }

    public function test_enforces_blog_limit_on_cloud(): void
    {
        // already at the plan's blog limit
        BlogFactory::createOne(['organization_id' => 1, 'type' => BlogType::DEFAULT]);

        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiCost: 0,
            seoAnalysis: false,
            linkAnalysis: false,
            blogs: 1,
            noBranding: false,
        );
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $personalLicense)],
        );

        $this->create(['name' => 'Second', 'subdomain' => 'blog-limit-cloud']);
        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'maximum number of blogs',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    public function test_sends_resource_created_event_on_cloud(): void
    {
        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiCost: 0,
            seoAnalysis: false,
            linkAnalysis: false,
            blogs: 1,
            noBranding: false,
        );
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $personalLicense)],
        );


        $this->create(['name' => 'Cloud Blog', 'subdomain' => 'cloud-resource-created']);
        $this->assertResponseIsSuccessful();

        $this->getComms()->assertSent(
            ResourceCreated::class,
            Component::CORE,
            eventValidator: function (ResourceCreated $event) {
                $this->assertSame(1, $event->getOrganizationId());
            },
        );
    }

}
