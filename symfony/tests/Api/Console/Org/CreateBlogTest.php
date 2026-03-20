<?php

namespace Api\Console\Org;

use App\Api\Console\Controller\BlogController;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Entity\Route;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Blog\BlogService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(BlogController::class)]
#[UsesClass(BlogService::class)]
class CreateBlogTest extends ApiTestCase
{

    private function doCreate(array $data, ?int $orgId = null): void
    {
        $orgId ??= 100;
        $user = AuthFake::generateUser(['id' => 200]);
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');
        $this->consoleOrgApi('POST', '/blog', data: $data, user: $user, organization: $org);
    }

    private function getEm(): EntityManagerInterface
    {
        return $this->getService(EntityManagerInterface::class);
    }

    private function getBlog(string $subdomain): Blog
    {
        $blog = $this->getEm()->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
        $this->assertNotNull($blog);
        return $blog;
    }

    public function test_creates_blog_and_returns_blog_list_object(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'myblog-unique']);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();

        $this->assertIsInt($json['id']);
        $this->assertSame('My Blog', $json['name']);
        $this->assertSame('myblog-unique', $json['subdomain']);
        $this->assertSame('owner', $json['role']);
        $this->assertSame('default', $json['type']);
        $this->assertArrayHasKey('url', $json);
    }

    public function test_creates_dev_blog_with_generated_subdomain(): void
    {
        $this->doCreate(['name' => 'Dev Blog', 'is_dev' => true]);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();

        $this->assertStringStartsWith('dev-', $json['subdomain']);
        $this->assertSame('dev', $json['type']);
    }

    public function test_sends_resource_created_comms_event(): void
    {
        $orgId = 150;
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'myblog-comms'], orgId: $orgId);

        $this->assertResponseStatusCodeSame(201);
        $this->getComms()->assertSent(ResourceCreated::class, Component::CORE);
    }

    public function test_returns_422_when_name_is_missing(): void
    {
        $this->doCreate(['subdomain' => 'valid-sub']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_422_when_subdomain_is_missing(): void
    {
        $this->doCreate(['name' => 'My Blog']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_422_for_reserved_subdomain(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'new']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_422_for_taken_subdomain(): void
    {
        BlogFactory::createOne(['subdomain' => 'already-taken']);
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'already-taken']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_422_for_invalid_subdomain_format(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'UPPERCASE']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_blog_is_persisted_in_database(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'persist-test']);

        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('persist-test');
        $this->assertSame(100, $blog->getOrganizationId());
    }

    // -----------------------------------------------------------------------
    // Seeded data — default blog
    // -----------------------------------------------------------------------

    public function test_default_blog_seeds_one_english_language(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'seed-lang-default']);
        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('seed-lang-default');
        $em = $this->getEm();

        $languages = $em->getRepository(Language::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $languages);
        $this->assertSame('en', $languages[0]->getCode());
        $this->assertTrue($languages[0]->isPrimary());
    }

    public function test_default_blog_seeds_owner_user(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'seed-user-default']);
        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('seed-user-default');
        $em = $this->getEm();

        $users = $em->getRepository(User::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $users);
        $this->assertSame('owner', $users[0]->getRole()->value);
        $this->assertSame(200, $users[0]->getHyvorUserId());
    }

    public function test_default_blog_seeds_one_welcome_tag(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'seed-tag-default']);
        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('seed-tag-default');
        $em = $this->getEm();

        $tags = $em->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $tags);
        $this->assertSame('welcome', $tags[0]->getSlug());
    }

    public function test_default_blog_seeds_five_routes(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'seed-routes-default']);
        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('seed-routes-default');
        $em = $this->getEm();

        $routes = $em->getRepository(Route::class)->findBy(['blog' => $blog]);
        $this->assertCount(5, $routes);

        $routeNames = array_map(fn(Route $r) => $r->getName(), $routes);
        $this->assertContains('post', $routeNames);
        $this->assertContains('page', $routeNames);
        $this->assertContains('index', $routeNames);
        $this->assertContains('tag', $routeNames);
        $this->assertContains('author', $routeNames);
    }

    public function test_default_blog_seeds_three_navigation_items(): void
    {
        $this->doCreate(['name' => 'My Blog', 'subdomain' => 'seed-nav-default']);
        $this->assertResponseStatusCodeSame(201);

        $blog = $this->getBlog('seed-nav-default');
        $em = $this->getEm();

        $navs = $em->getRepository(Navigation::class)->findBy(['blog' => $blog]);
        $this->assertCount(3, $navs);
    }

    // -----------------------------------------------------------------------
    // Seeded data — dev blog
    // -----------------------------------------------------------------------

    public function test_dev_blog_seeds_three_languages(): void
    {
        $this->doCreate(['name' => 'Dev Blog', 'is_dev' => true]);
        $this->assertResponseStatusCodeSame(201);

        $json = $this->getJson();
        $blog = $this->getBlog($json['subdomain']);
        $em = $this->getEm();

        $languages = $em->getRepository(Language::class)->findBy(['blog' => $blog]);
        $this->assertCount(3, $languages);

        $codes = array_map(fn(Language $l) => $l->getCode(), $languages);
        sort($codes);
        $this->assertSame(['ar', 'en', 'fr'], $codes);
    }

    public function test_dev_blog_seeds_owner_plus_five_guest_users(): void
    {
        $this->doCreate(['name' => 'Dev Blog', 'is_dev' => true]);
        $this->assertResponseStatusCodeSame(201);

        $json = $this->getJson();
        $blog = $this->getBlog($json['subdomain']);
        $em = $this->getEm();

        $users = $em->getRepository(User::class)->findBy(['blog' => $blog]);
        $this->assertCount(6, $users);

        $owners = array_filter($users, fn(User $u) => $u->getRole() === \App\Entity\Enum\UserRole::OWNER);
        $this->assertCount(1, $owners);
    }

    public function test_dev_blog_seeds_six_tags(): void
    {
        $this->doCreate(['name' => 'Dev Blog', 'is_dev' => true]);
        $this->assertResponseStatusCodeSame(201);

        $json = $this->getJson();
        $blog = $this->getBlog($json['subdomain']);
        $em = $this->getEm();

        $tags = $em->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(6, $tags);
    }
}
