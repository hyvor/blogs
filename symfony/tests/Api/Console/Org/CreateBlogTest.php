<?php

namespace App\Tests\Api\Console\Org;

use App\Api\Console\ControllerOrg\BlogController;
use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeCreationType;
use App\Entity\Enum\UserRole;
use App\Entity\HyvorPost;
use App\Entity\HyvorTalkWebsite;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Route;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Blog\BlogCreator;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
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
use Hyvor\Sdk\Exceptions\NetworkException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogCreator::class)]
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

    private function create(array $data): Response
    {
        $user = AuthFake::generateUser(['id' => 501]);
        $org = new AuthUserOrganization(1, 'Test Org', 'admin');
        return $this->consoleOrgApi('POST', '/blog', $data, user: $user, organization: $org);
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
        $this->assertSame(2, $blog->getCounts()['posts']);

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
            'https://blogs.hyvor.com/docs/writing',
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
    }

    public function test_creates_dev_blog(): void
    {
        $this->enableOnPremise();
        $this->create(['name' => 'Dev Blog', 'is_dev' => true]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('dev', $json['type']);
        /** @var string $subdomain */
        $subdomain = $json['subdomain'];
        $this->assertMatchesRegularExpression('/^dev-[0-9a-f-]{36}$/i', $subdomain);

        $blog = $this->getEm()->getRepository(Blog::class)->find($json['id']);
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

        // the 5 base posts + 30 random posts
        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(35, $posts);

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
            aiTokens: 0,
            autoTranslationsChars: 0,
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

    public function test_enforces_blog_limit_on_cloud(): void
    {
        // already at the plan's blog limit
        BlogFactory::createOne(['organization_id' => 1, 'type' => BlogType::DEFAULT]);

        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiTokens: 0,
            autoTranslationsChars: 0,
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
            aiTokens: 0,
            autoTranslationsChars: 0,
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

    public function test_creates_with_hyvor_post(): void
    {
        $hpMock = $this->createMock(HyvorPostService::class);
        $hp = new HyvorPost();
        $hp->setNewsletterId(123);
        $hpMock->expects($this->once())->method('connect')->willReturn($hp);
        $this->getContainer()->set(HyvorPostService::class, $hpMock);

        $license = BlogsLicense::trial();
        BillingFake::enableForSymfony($this->getContainer(), [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $license)]);

        $this->create(['name' => 'My Blog', 'subdomain' => 'new-blog', 'hyvor_post' => true]);
        $this->assertResponseIsSuccessful();
    }

    public function test_sets_warning_if_hyvor_post_fails(): void
    {
        $hpMock = $this->createMock(HyvorPostService::class);
        $hpMock->expects($this->once())->method('connect')->willThrowException(new NetworkException('Hyvor Post error'));
        $this->getContainer()->set(HyvorPostService::class, $hpMock);

        $license = BlogsLicense::trial();
        BillingFake::enableForSymfony($this->getContainer(), [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $license)]);

        $this->create(['name' => 'My Blog', 'subdomain' => 'new-blog', 'hyvor_post' => true]);
        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('warnings', $json);
        $warnings = $json['warnings'];
        $this->assertIsArray($warnings);
        $warningStrings = [];
        foreach ($warnings as $warning) {
            $this->assertIsString($warning);
            $warningStrings[] = $warning;
        }
        $this->assertStringContainsString('Failed to connect to Hyvor Post', implode(' ', $warningStrings));
    }

    public function test_creates_with_hyvor_talk(): void
    {
        $htMock = $this->createMock(HyvorTalkService::class);
        $htWebsite = new HyvorTalkWebsite();
        $htWebsite->setWebsiteId(123);
        $htMock->expects($this->once())->method('connect')->willReturn($htWebsite);
        $this->getContainer()->set(HyvorTalkService::class, $htMock);

        $license = BlogsLicense::trial();
        $billingFake = $this->getService(BillingFake::class);
        $billingFake->setLicenses([1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $license)]);

        $this->create(['name' => 'My Blog', 'subdomain' => 'new-blog', 'hyvor_talk' => true]);
        $this->assertResponseIsSuccessful();
    }

    public function test_sets_warning_if_hyvor_talk_fails(): void
    {
        $htMock = $this->createMock(HyvorTalkService::class);
        $htMock->expects($this->once())->method('connect')->willThrowException(new NetworkException('Hyvor Post error'));
        $this->getContainer()->set(HyvorTalkService::class, $htMock);

        $license = BlogsLicense::trial();
        $billingFake = $this->getService(BillingFake::class);
        $billingFake->setLicenses([1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $license)]);

        $this->create(['name' => 'My Blog', 'subdomain' => 'new-blog', 'hyvor_talk' => true]);
        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('warnings', $json);
        $warnings = $json['warnings'];
        $this->assertIsArray($warnings);
        $warningStrings = [];
        foreach ($warnings as $warning) {
            $this->assertIsString($warning);
            $warningStrings[] = $warning;
        }
        $this->assertStringContainsString('Failed to connect to Hyvor Talk', implode(' ', $warningStrings));
    }

}
