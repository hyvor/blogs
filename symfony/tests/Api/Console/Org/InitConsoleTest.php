<?php

namespace Api\Console\Org;

use App\Api\Console\ControllerOrg\ConsoleController;
use App\Api\Console\Object\AuthUserObject;
use App\Api\Console\Object\BlogListObject;
use App\Api\Console\Object\BlogListObjectFactory;
use App\Entity\Enum\UserRole;
use App\Service\CodeHighlight\Highlighter;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AuthUserObject::class)]
#[CoversClass(BlogListObject::class)]
#[CoversClass(BlogListObjectFactory::class)]
#[CoversClass(ConsoleController::class)]
#[CoversClass(Highlighter::class)]
class InitConsoleTest extends ApiTestCase
{

    public function test_returns_user_and_blogs_when_authenticated(): void
    {
        $orgId = 100;
        $hyvorUserId = 200;

        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'organization_id' => $orgId,
                'subdomain' => 'myblog',
            ],
            [
                'hyvor_user_id' => $hyvorUserId,
                'role' => UserRole::OWNER,
            ],
        );

        // other user blog
        $blog2 = BlogFactory::createOneWithUser(['organization_id' => $orgId]);

        $authUser = AuthFake::generateUser(['id' => $hyvorUserId]);
        $authOrg = new AuthUserOrganization($orgId, 'My Org', 'admin');

        $this->consoleOrgApi('GET', '/init', user: $authUser, organization: $authOrg);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['user']);
        $this->assertIsArray($json['organization']);

        $this->assertSame($hyvorUserId, $json['user']['id']);
        $this->assertSame($orgId, $json['organization']['id']);

        $this->assertIsArray($json['blogs']);
        $this->assertCount(1, $json['blogs']);
        $this->assertIsArray($json['blogs'][0]);
        $this->assertSame('owner', $json['blogs'][0]['role']);
        $this->assertSame('myblog', $json['blogs'][0]['subdomain']);

        $this->assertIsArray($json['config']);
        $this->assertArrayHasKey('limits', $json['config']);
        $this->assertArrayHasKey('highlight_themes', $json['config']);
    }

    public function test_returns_empty_blogs_if_no_organization(): void
    {
        $authUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleOrgApi('GET', '/init', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame([], $json['blogs']);
        $this->assertNull($json['organization']);
    }

    public function test_preloads_first_blog(): void
    {

        $orgId = 100;
        $hyvorUserId = 200;

        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'organization_id' => $orgId,
                'subdomain' => 'myblog',
            ],
            [
                'hyvor_user_id' => $hyvorUserId,
                'role' => UserRole::OWNER,
            ],
        );
        LanguageFactory::createOnePrimaryFor($blog);

        $authUser = AuthFake::generateUser(['id' => $hyvorUserId]);
        $authOrg = new AuthUserOrganization($orgId, 'My Org', 'admin');

        $this->consoleOrgApi('GET', '/init', user: $authUser, organization: $authOrg);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['preloaded']['blog']);
        $this->assertSame('myblog', $json['preloaded']['blog']['blog']['subdomain']);
    }

    public function test_preloads_blog_matching_blog_hint(): void
    {
        $orgId = 100;
        $hyvorUserId = 200;

        [$blog1, $user] = BlogFactory::createOneWithUser(
            [
                'organization_id' => $orgId,
                'subdomain' => 'blog1',
            ],
            [
                'hyvor_user_id' => $hyvorUserId,
                'role' => UserRole::OWNER,
            ],
        );
        LanguageFactory::createOnePrimaryFor($blog1);

        [$blog2, ] = BlogFactory::createOneWithUser(
            [
                'organization_id' => $orgId,
                'subdomain' => 'blog2',
            ],
            [
                'hyvor_user_id' => $hyvorUserId,
                'role' => UserRole::OWNER,
            ],
        );
        LanguageFactory::createOnePrimaryFor($blog2);

        $authUser = AuthFake::generateUser(['id' => $hyvorUserId]);
        $authOrg = new AuthUserOrganization($orgId, 'My Org', 'admin');

        $this->consoleOrgApi('GET', '/init?blog_hint=blog2', user: $authUser, organization: $authOrg);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('blog2', $json['preloaded']['blog']['blog']['subdomain']);
    }
}
