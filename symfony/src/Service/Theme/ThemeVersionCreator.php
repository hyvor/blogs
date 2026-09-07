<?php

namespace App\Service\Theme;

use App\Entity\Enum\BlogType;
use App\Entity\Theme;
use App\Entity\ThemeVersion;
use App\Service\Blog\BlogCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class ThemeVersionCreator {

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private BlogCreator $blogCreator,
        private ThemeFilesService $themeFilesService,
    ) {}

    public function create(
        Theme $theme,
        string $version,
        string $zip,
        bool $createPreviewBlog = true
    ): ThemeVersion
    {
        return $this->em->wrapInTransaction(function() use ($theme, $version, $zip, $createPreviewBlog) {

            $themeVersion = new ThemeVersion();
            $themeVersion->setCreatedAt($this->clock->now());
            $themeVersion->setUpdatedAt($this->clock->now());
            $themeVersion->setTheme($theme);
            $themeVersion->setVersion($version);
            $themeVersion->setZip($zip);

            $this->em->persist($themeVersion);
            $this->em->flush();

            if ($createPreviewBlog) {
                $previewSubdomain = $this->generateThemePreviewSubdomain($theme->getName(), $version);

                $blog = $this->blogCreator->create(
                    null,
                    null,
                    $theme->getName(),
                    $previewSubdomain,
                    BlogType::PREVIEW
                )['blog'];

                $themeVersion->setPreviewSubdomain($previewSubdomain);
                $this->em->flush();

                $this->themeFilesService->updateFilesFromThemeVersion($blog, $themeVersion);
            }

            return $themeVersion;

        });
    }


    private function generateThemePreviewSubdomain(string $name, string $version): string
    {
        $version = str_replace('.', '-', $version);
        $random = bin2hex(random_bytes(6));

        return "theme-$name-$version-$random";
    }
}
