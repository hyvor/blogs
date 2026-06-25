<?php

namespace App\Service\Theme;

use App\Entity\Theme;
use App\Entity\ThemeVersion;
use Doctrine\ORM\EntityManagerInterface;

class ThemeService
{
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
            $versionsByThemeId[$version->getThemeId()] = $version;
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
}
