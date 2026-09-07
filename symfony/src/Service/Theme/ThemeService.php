<?php

namespace App\Service\Theme;

use App\Entity\Enum\ThemeCreationType;
use App\Entity\Theme;
use App\Entity\ThemeVersion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class ThemeService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /** @return Theme[] */
    public function getAllThemes(): array
    {
        return $this->em->getRepository(Theme::class)->findAll();
    }

    /**
     * Returns all themes paired with their latest version (or null if none).
     *
     * @return array<int, array{theme: Theme, latest_version: ?ThemeVersion}>
     */
    public function getAllThemesWithLatestVersions(): array
    {
        $themes = $this->getAllThemes();

        if ($themes === []) {
            return [];
        }

        $themeIds = array_map(fn(Theme $t) => $t->getId(), $themes);

        /** @var ThemeVersion[] $versions */
        $versions = $this->em->createQuery(
            'SELECT tv FROM App\Entity\ThemeVersion tv
             WHERE tv.id IN (
                 SELECT MAX(tv2.id) FROM App\Entity\ThemeVersion tv2
                 WHERE tv2.theme IN (:themeIds)
                 GROUP BY tv2.theme
             )'
        )
            ->setParameter('themeIds', $themeIds)
            ->getResult();

        $versionsByThemeId = [];
        foreach ($versions as $version) {
            $versionsByThemeId[$version->getTheme()->getId()] = $version;
        }

        $result = [];
        foreach ($themes as $theme) {
            $result[] = [
                'theme' => $theme,
                'latest_version' => $versionsByThemeId[$theme->getId()] ?? null,
            ];
        }

        return $result;
    }

    public function getThemeByName(string $name): ?Theme
    {
        return $this->em->getRepository(Theme::class)->findOneBy(['name' => $name]);
    }

    public function getThemeLatestVersion(Theme $theme): ?ThemeVersion
    {
        return $this->em->getRepository(ThemeVersion::class)->findOneBy(
            ['theme' => $theme],
            ['id' => 'DESC'],
        );
    }

    public function getThemeVersion(Theme $theme, string $version): ?ThemeVersion
    {
        return $this->em->getRepository(ThemeVersion::class)->findOneBy([
            'theme' => $theme,
            'version' => $version,
        ]);
    }

    public function createTheme(string $name, ThemeCreationType $type): Theme
    {
        $theme = new Theme();
        $theme->setCreatedAt($this->clock->now());
        $theme->setUpdatedAt($this->clock->now());
        $theme->setName($name);
        $theme->setType($type);
        $this->em->persist($theme);
        $this->em->flush();
        return $theme;
    }

    /**
     * Returns name => latest version string for all themes that have at least one version.
     *
     * @return array<string, string>
     */
    public function getLatestVersionsOfAllThemes(): array
    {
        /** @var ThemeVersion[] $versions */
        $versions = $this->em->createQuery(
            'SELECT tv FROM App\Entity\ThemeVersion tv
             WHERE tv.id IN (
                 SELECT MAX(tv2.id) FROM App\Entity\ThemeVersion tv2
                 GROUP BY tv2.theme
             )'
        )->getResult();

        $result = [];
        foreach ($versions as $version) {
            $result[$version->getTheme()->getName()] = $version->getVersion();
        }
        return $result;
    }
}
