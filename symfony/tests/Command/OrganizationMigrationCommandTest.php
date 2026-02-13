<?php

namespace App\Tests\Command;


use App\Command\OrganizationMigrationCommand;
use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\EnsureMembers;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrg;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrgResponse;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[CoversClass(OrganizationMigrationCommand::class)]
class OrganizationMigrationCommandTest extends KernelTestCase
{

    use ClockSensitiveTrait;

    public function test_organization_migration(): void
    {
        $this->mockTime();

        $blogs = BlogFactory::createMany(3, [
            'organization_id' => null,
        ]);

        $this->getComms()->addResponse(InitOrg::class, function () {
            return new InitOrgResponse(rand());
        });

        $shouldReceiveEnsureMembers = [];
        foreach ($blogs as $blog) {
            $user = UserFactory::createOne([
                'blog' => $blog,
                'hyvor_user_id' => $blog->getHyvorUserId(),
                'role' => UserRole::OWNER,
            ]);
            $admin = UserFactory::createOne([
                'blog' => $blog,
                'role' => UserRole::ADMIN,
            ]);

            $userIds = [$user->getHyvorUserId(), $admin->getHyvorUserId()];
            sort($userIds);

            $shouldReceiveEnsureMembers[] = [
                'blog' => $blog,
                'userIds' => $userIds,
            ];
        }

        $command = $this->commandTester('organization:migrate');
        $exitCode = $command->execute([]);
        $this->assertSame(0, $exitCode);

        $this->getComms()->assertSent(InitOrg::class, Component::CORE);

        /** @var array<int, array{event: EnsureMembers}> $sentEvents */
        $sentEvents = $this->getComms()->getSentsByEventClass(EnsureMembers::class);
        $this->assertCount(3, $sentEvents);

        foreach ($shouldReceiveEnsureMembers as $receivable) {
            $event = array_values(
                array_filter(
                    $sentEvents,
                    fn(array $item) => $item['event']->orgId === $receivable['blog']->getOrganizationId(),
                ),
            )[0];

            $this->assertSame($receivable['userIds'], $event['event']->userIds);
        }

        // Assert everything is updated
        $pendingBlogs = $this->getEm()->getRepository(Blog::class)->findBy([
            'organization_id' => null,
        ]);
        $this->assertCount(0, $pendingBlogs);
    }

    public function test_does_not_update_migrated_organizations(): void
    {
        $this->mockTime();

        $blogs = BlogFactory::createMany(3, [
            'organization_id' => null,
        ]);

        $this->getComms()->addResponse(InitOrg::class, function () {
            return new InitOrgResponse(20000310);
        });

        foreach ($blogs as $blog) {
            UserFactory::createOne([
                'blog' => $blog,
                'hyvor_user_id' => $blog->getHyvorUserId(),
                'role' => UserRole::OWNER,
            ]);
            UserFactory::createOne([
                'blog' => $blog,
                'role' => UserRole::ADMIN,
            ]);
        }


        $migratedBlogs = BlogFactory::createMany(2, [
            'organization_id' => 20001003,
        ]);
        foreach ($migratedBlogs as $blog) {
            UserFactory::createOne([
                'blog' => $blog,
                'hyvor_user_id' => $blog->getHyvorUserId(),
                'role' => UserRole::OWNER,
            ]);
            UserFactory::createOne([
                'blog' => $blog,
                'role' => UserRole::ADMIN,
            ]);
        }

        $command = $this->commandTester('organization:migrate');
        $exitCode = $command->execute([]);
        $this->assertSame(0, $exitCode);

        // Assert nothing is updated
        $dbBlogs = $this->getEm()->getRepository(Blog::class)->findBy([
            'organization_id' => 20001003,
        ]);
        $this->assertCount(2, $dbBlogs);
    }
}
