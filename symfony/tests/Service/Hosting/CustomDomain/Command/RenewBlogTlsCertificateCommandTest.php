<?php

namespace App\Tests\Service\Hosting\CustomDomain\Command;

use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Command\RenewBlogTlsCertificateCommand;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(RenewBlogTlsCertificateCommand::class)]
class RenewBlogTlsCertificateCommandTest extends KernelTestCase
{
    public function test_renews_certificate_of_blog_selected_by_subdomain(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'renew-cmd-blog']);
        $customDomain = CustomDomainFactory::createActiveFor($blog, 'renew-cmd.example.com', [
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            'valid_to' => new \DateTimeImmutable('+10 days'),
        ]);

        $service = $this->createMock(CustomDomainService::class);
        $service->method('getBlogCustomDomain')->willReturn($customDomain);
        $service->expects($this->once())
            ->method('generateCertificate')
            ->with($this->callback(fn(CustomDomain $cd) => $cd->getId() === $customDomain->getId()))
            ->willReturn($customDomain);
        static::getContainer()->set(CustomDomainService::class, $service);

        $commandTester = $this->getCommandTester('hosting:renew-tls-certificate');
        $commandTester->execute(['--subdomain' => 'renew-cmd-blog']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Certificate renewed for renew-cmd.example.com', $commandTester->getDisplay());
    }
}
