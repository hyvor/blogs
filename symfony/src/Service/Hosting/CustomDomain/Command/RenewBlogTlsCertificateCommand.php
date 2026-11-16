<?php

namespace App\Service\Hosting\CustomDomain\Command;

use App\Entity\Blog;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'hosting:renew-tls-certificate',
    description: "Renews a specific blog's custom domain TLS certificate (select the blog by --id or --subdomain)",
)]
class RenewBlogTlsCertificateCommand
{
    public function __construct(
        private EntityManagerInterface $em,
        private CustomDomainService $customDomainService,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('blog ID')] ?int $id = null,
        #[Option('blog subdomain')] ?string $subdomain = null,
        #[Option('renew even if the certificate is not close to expiring')] bool $force = false,
    ): int {
        $io = new SymfonyStyle($input, $output);

        if (($id === null) === ($subdomain === null)) {
            $io->error('Provide exactly one of --id or --subdomain.');
            return Command::INVALID;
        }

        $blog = $id !== null
            ? $this->em->getRepository(Blog::class)->find($id)
            : $this->em->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);

        if ($blog === null || $blog->getDeletedAt() !== null) {
            $io->error('Blog not found.');
            return Command::FAILURE;
        }

        $io->writeln(sprintf('Blog #%d (subdomain: %s)', $blog->getId(), $blog->getSubdomain()));

        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);
        if ($customDomain === null) {
            $io->error('This blog has no custom domain.');
            return Command::FAILURE;
        }

        $io->definitionList(
            ['Domain' => $customDomain->getDomain()],
            ['TLS provider' => $customDomain->getTlsProvider()->value],
            ['Valid from' => $customDomain->getValidFrom()?->format(DATE_ATOM) ?? 'n/a'],
            ['Valid to' => $customDomain->getValidTo()?->format(DATE_ATOM) ?? 'n/a'],
        );

        if ($customDomain->getTlsProvider() !== CustomDomainTlsProvider::AUTO) {
            $io->error('The TLS certificate is managed by the user (provider: custom); it cannot be renewed automatically.');
            return Command::FAILURE;
        }

        $validTo = $customDomain->getValidTo();
        $threshold = new \DateTimeImmutable('+30 days');
        if (!$force && $validTo !== null && $validTo >= $threshold) {
            $io->warning('Certificate is not due for renewal (expires in more than 30 days). Use --force to renew anyway.');
            return Command::SUCCESS;
        }

        $io->writeln('Requesting a new certificate...');

        try {
            $this->customDomainService->generateCertificate($customDomain);
        } catch (AcmeException $e) {
            $io->error('Renewal failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Certificate renewed for %s. Valid from %s to %s.',
            $customDomain->getDomain(),
            $customDomain->getValidFrom()?->format(DATE_ATOM) ?? 'n/a',
            $customDomain->getValidTo()?->format(DATE_ATOM) ?? 'n/a',
        ));

        return Command::SUCCESS;
    }
}
