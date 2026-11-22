<?php

namespace App\Command\App;

use App\Service\AppConfig;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\Oidc\OidcApiServiceFactory;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Deployment;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Util\Crypt\Encryption;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsCommand(
    name: 'app:verify',
    description: 'Verifies the application setup and configuration.',
)]
class AppVerifyCommand
{

    public function __construct(
        private AppConfig $appConfig,
        private InternalConfig $internalConfig,
        private EntityManagerInterface $em,
        private Encryption $encryption,
        private OidcConfig $oidcConfig,
        private OidcApiServiceFactory $oidcApiServiceFactory,
        private Filesystem $filesystem,
        private HubInterface $hub,
        #[Autowire('%kernel.environment%')]
        private string $environment,
        #[Autowire('%env(string:FILESYSTEM)%')]
        private string $filesystemAdapter,
        #[Autowire('%env(TRUSTED_PROXIES)%')]
        private string $trustedProxies,
        #[Autowire('%env(LOG_LEVEL)%')]
        private string $logLevel,
        #[Autowire('%env(string:default::MAIL_HOST)%')]
        private string $mailHost,
        #[Autowire('%env(string:default::MAIL_PORT)%')]
        private string $mailPort,
        #[Autowire('%env(string:default::MAIL_USERNAME)%')]
        #[\SensitiveParameter]
        private string $mailUsername,
        #[Autowire('%env(string:default::MAIL_PASSWORD)%')]
        #[\SensitiveParameter]
        private string $mailPassword,
    ) {
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Configuration');
        $io->table(['Config', 'Value'], [
            ['App Version', $this->appConfig->getVersion()],
            ['App Environment', $this->environment],
            ['App Domain (DOMAIN_APP)', $this->appConfig->getDomainApp()],
            ['Delivery URL', $this->appConfig->getDeliveryUrl() ?? 'not set'],
            ['FileSystem', $this->filesystemAdapter],
            ['Trusted Proxies', $this->trustedProxies],
            ['Log Level', $this->logLevel],
            ['Deployment', $this->internalConfig->getDeployment()->value],
        ]);

        $io->section('Checks');

        $failed = false;

        $checks = [
            'Database' => fn() => $this->checkDatabase(),
            'Encryption / App Secret' => fn() => $this->checkEncryption(),
            'OIDC' => fn() => $this->checkOidc(),
            'S3' => fn() => $this->checkS3(),
            'Mailer' => fn() => $this->checkMailer(),
            'Mercure' => fn() => $this->checkMercure(),
        ];

        foreach ($checks as $label => $check) {
            $io->write(sprintf('%-30s', $label));
            $result = $check();
            $io->writeln($this->formatResult($result));

            if (str_starts_with($result, 'FAILED')) {
                $failed = true;
            }
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }

    private function formatResult(string $result): string
    {
        return match (true) {
            $result === 'OK' => '<fg=green>OK</>',
            $result === 'SKIPPED' => '<fg=yellow>SKIPPED</>',
            default => '<fg=red>' . $result . '</>',
        };
    }

    private function checkDatabase(): string
    {
        try {
            $this->em->getConnection()->fetchOne('SELECT 1');
            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

    private function checkEncryption(): string
    {
        try {
            $testString = 'app:verify_test_string';
            $encrypted = $this->encryption->encryptString($testString);
            $decrypted = $this->encryption->decryptString($encrypted);

            if ($decrypted !== $testString) {
                return 'FAILED: Decrypted value does not match original.';
            }

            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

    private function checkOidc(): string
    {
        if ($this->internalConfig->getDeployment() !== Deployment::ON_PREM) {
            return 'SKIPPED';
        }

        if ($this->oidcConfig->getIssuerUrl() === '') {
            return 'SKIPPED';
        }

        try {
            $this->oidcApiServiceFactory->create($this->oidcConfig)->getWellKnownConfig();
            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

    private function checkS3(): string
    {
        if ($this->filesystemAdapter !== 's3') {
            return 'SKIPPED';
        }

        $path = 'app-verify-' . bin2hex(random_bytes(8)) . '.tmp';

        try {
            $this->filesystem->write($path, 'app:verify');

            if ($this->filesystem->read($path) !== 'app:verify') {
                return 'FAILED: Read value does not match written value.';
            }

            $this->filesystem->delete($path);

            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

    private function checkMailer(): string
    {
        if ($this->mailHost === '') {
            return 'SKIPPED';
        }

        try {
            $dsn = sprintf(
                'smtp://%s:%s@%s:%s',
                $this->mailUsername,
                $this->mailPassword,
                $this->mailHost,
                $this->mailPort,
            );
            $transport = Transport::fromDsn($dsn);

            if (!$transport instanceof SmtpTransport) {
                return 'SKIPPED';
            }

            $transport->start();
            $transport->stop();
            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

    private function checkMercure(): string
    {
        try {
            $this->hub->publish(new Update('app:verify', 'app:verify'));
            return 'OK';
        } catch (\Exception $e) {
            return 'FAILED: ' . $e->getMessage();
        }
    }

}
