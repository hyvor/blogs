<?php

namespace App\Tests\Service\Theme\RepoSync;

use App\Entity\Enum\ThemeCreationType;
use App\Entity\Theme;
use App\Entity\ThemeVersion;
use App\Service\Theme\RepoSync\RepoSyncService;
use App\Tests\Factory\ThemeFactory;
use App\Tests\Factory\ThemeVersionFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepoSyncService::class)]
class RepoSyncTest extends KernelTestCase
{
    private function zipPath(): string
    {
        return __DIR__ . '/../../../__DATA__/Themes/github-themes.zip';
    }

    private function service(): RepoSyncService
    {
        return $this->getService(RepoSyncService::class);
    }

    public function test_adds_new_themes_and_versions(): void
    {
        $this->service()->syncFromFile($this->zipPath());

        $em = $this->getEm();
        $themes = $em->getRepository(Theme::class)->findAll();

        $this->assertGreaterThan(0, count($themes));

        foreach ($themes as $theme) {
            $version = $em->getRepository(ThemeVersion::class)->findOneBy(['theme' => $theme]);
            $this->assertNotNull($version);
        }
    }

    public function test_updates_theme_when_version_is_new(): void
    {
        ThemeFactory::createOne(['name' => 'hello', 'type' => ThemeCreationType::ORIGINAL]);
        $theme = $this->getEm()->getRepository(Theme::class)->findOneBy(['name' => 'hello']);
        ThemeVersionFactory::createOne(['theme' => $theme, 'version' => '0.0.1']);

        $this->service()->syncFromFile($this->zipPath());

        $count = $this->getEm()
            ->getRepository(ThemeVersion::class)
            ->count(['theme' => $theme]);

        $this->assertSame(2, $count);
    }

    public function test_does_not_update_when_version_is_the_same(): void
    {
        ThemeFactory::createOne(['name' => 'hello', 'type' => ThemeCreationType::ORIGINAL]);
        $theme = $this->getEm()->getRepository(Theme::class)->findOneBy(['name' => 'hello']);
        ThemeVersionFactory::createOne(['theme' => $theme, 'version' => '1.0.0']);

        $this->service()->syncFromFile($this->zipPath());

        $count = $this->getEm()
            ->getRepository(ThemeVersion::class)
            ->count(['theme' => $theme]);

        $this->assertSame(1, $count);
    }

    public function test_break_into_themes_parses_zip_correctly(): void
    {
        $service = $this->service();
        $service->breakIntoThemes($this->zipPath());

        $this->assertArrayHasKey('hello', $service->themes);
        $this->assertArrayHasKey('blank', $service->themes);

        $hello = $service->themes['hello'];
        $this->assertNotNull($hello->findFile(null, 'config.yaml'));
        $this->assertSame('1.0.0', $hello->getVersion());
    }
}
