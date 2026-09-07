<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Service\Billing\LicenseService;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateRendererService::class)]
#[CoversClass(LicenseService::class)]
class BrandingTest extends KernelTestCase
{

    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_shows_branding(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'organization_id' => 1,
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{%if _branding %}Branding is shown{%else%}Branding is hidden{%endif%}');

        $license = BlogsLicense::trial();
        $license->noBranding = false;

        $this->getService(BillingFake::class)->setLicenses([
            1 => new ResolvedLicense(
                ResolvedLicenseType::SUBSCRIPTION,
                $license
            )
        ]);

        $response = $this->pathMatcher()->match($blog, '/');
        $content = (string)$response->content;
        $this->assertSame('Branding is shown', $content);
    }

    public function test_hides_branding_based_on_license(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'organization_id' => 1,
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{%if _branding %}Branding is shown{%else%}Branding is hidden{%endif%}');

        $license = BlogsLicense::trial();
        $license->noBranding = true;

        $this->getService(BillingFake::class)->setLicenses([
            1 => new ResolvedLicense(
                ResolvedLicenseType::SUBSCRIPTION,
                $license
            )
        ]);

        $response = $this->pathMatcher()->match($blog, '/');
        $content = (string)$response->content;
        $this->assertSame('Branding is hidden', $content);
    }

    public function test_shows_on_prem(): void
    {
        $this->setEnvVar('DEPLOYMENT', 'on-prem');

        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'organization_id' => 1,
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{%if _branding %}Branding is shown{%else%}Branding is hidden{%endif%}');

        $response = $this->pathMatcher()->match($blog, '/');
        $content = (string)$response->content;
        $this->assertSame('Branding is shown', $content);
    }

}
