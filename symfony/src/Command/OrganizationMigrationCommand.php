<?php

namespace App\Command;

use App\Entity\Blog;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\EnsureMembers;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrg;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrgResponse;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'organization:migrate',
    description: 'Migrate organization data.'
)]
class OrganizationMigrationCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $em,
        private CommsInterface $comms,
        private KernelInterface $kernel,
        private ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            /** @var Blog[] $blogsWithoutOrg */
            $blogsWithoutOrg = $this->em
                ->getRepository(Blog::class)
                ->createQueryBuilder('b')
                ->where('b.organization_id IS NULL')
                ->andWhere('b.hyvor_user_id IS NOT NULL')
                ->orderBy('b.id', 'ASC')
                ->setMaxResults(100)
                ->getQuery()
                ->getResult();

            if ($this->kernel->getEnvironment() === 'test' && count($blogsWithoutOrg) === 0) {
                $output->writeln("{$this->clock->now()->format('Y-m-d H:i:s')}: No more users to update. Exiting.");
                break;
            }

            foreach ($blogsWithoutOrg as $blog) {

                assert($blog->getHyvorUserId() !== null);

                $output->writeln(
                    "{$this->clock->now()->format('Y-m-d H:i:s')}: Updating Blog => User ID: {$blog->getHyvorUserId()}",
                );

                try {
                    $this->em->wrapInTransaction(function () use ($blog) {
                        $initOrgEvent = new InitOrg($blog->getHyvorUserId());
                        /** @var InitOrgResponse $initOrgResponse */
                        $initOrgResponse = $this->comms->send($initOrgEvent);

                        $createdOrgId = $initOrgResponse->orgId;

                        $this->migrateBlogToOrganization($blog, $createdOrgId);
                        $this->ensureMembersOfOrganization($createdOrgId);
                    });
                } catch (CommsApiFailedException | \Exception $e) {
                    $output->writeln(
                        "<error>Error occurred while migrating to organization. Blog ID: {$blog->getId()} | User ID: {$blog->getHyvorUserId()}</error>",
                    );
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            $output->writeln(
                "{$this->clock->now()->format('Y-m-d H:i:s')}: Updated " . count($blogsWithoutOrg) . " users\n\n\n",
            );
            $this->clock->sleep(2);
        }

        return Command::SUCCESS;
    }

    private function migrateBlogToOrganization(Blog $blog, int $organizationId): void
    {
        $blog->setOrganizationId($organizationId);
        $this->em->persist($blog);
        $this->em->flush();
    }

    /**
     * @throws Exception|CommsApiFailedException
     */
    private function ensureMembersOfOrganization(int $organizationId): void
    {
        $conn = $this->em->getConnection();
        /** @var string[] $userIds */
        $userIds = $conn->fetchFirstColumn(
            <<<SQL
                SELECT DISTINCT u.hyvor_user_id
                FROM users u
                JOIN blogs b ON b.id = u.blog_id
                WHERE b.organization_id = :orgId
                SQL,
            [
                'orgId' => $organizationId,
            ],
        );

        if (count($userIds) === 0) {
            return;
        }

        $userIds = array_map('intval', $userIds);

        $ensureMembersEvent = new EnsureMembers(
            $organizationId,
            $userIds,
        );
        $this->comms->send($ensureMembersEvent);
    }
}
