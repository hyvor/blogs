<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Theme;
use App\Entity\ThemeFile;
use App\Entity\ThemeVersion;
use Doctrine\ORM\EntityManagerInterface;

class ThemeSeeder
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Copies the appropriate theme to the blog.
     * - PREVIEW blogs: no theme
     * - DEV blogs: "blank" theme
     * - DEFAULT/TEMP blogs: "hello" theme
     */
    public function seed(Blog $blog): void
    {
        if ($blog->getType() === BlogType::PREVIEW) {
            return;
        }

        $themeName = $blog->getType() === BlogType::DEV ? 'blank' : 'hello';

        $theme = $this->em->getRepository(Theme::class)->findOneBy(['name' => $themeName]);
        if (!$theme) {
            return;
        }

        /** @var ThemeVersion|null $themeVersion */
        $themeVersion = $this->em->getRepository(ThemeVersion::class)
            ->createQueryBuilder('tv')
            ->where('tv.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('tv.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$themeVersion) {
            return;
        }

        $zip = $themeVersion->getZip();
        if (!$zip) {
            return;
        }

        $zipContent = is_resource($zip) ? stream_get_contents($zip) : $zip;
        if (!$zipContent) {
            return;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'theme_');
        file_put_contents($tmpFile, $zipContent);

        $archive = new \ZipArchive();
        if ($archive->open($tmpFile) !== true) {
            unlink($tmpFile);
            return;
        }

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $entryName = $archive->getNameIndex($i);
            if ($entryName === false || str_ends_with($entryName, '/')) {
                continue;
            }

            $content = $archive->getFromIndex($i);
            $parts = explode('/', $entryName, 2);
            [$folder, $name] = count($parts) === 2 ? [$parts[0], $parts[1]] : [null, $entryName];

            $themeFile = new ThemeFile();
            $themeFile->setBlog($blog);
            $themeFile->setBlogId($blog->getId());
            $themeFile->setFolder($folder !== '' ? $folder : null);
            $themeFile->setName($name);
            $themeFile->setContent($content !== false ? $content : null);
            $this->em->persist($themeFile);
        }

        $archive->close();
        unlink($tmpFile);

        $blog->setThemeVersionId($themeVersion->getId());
        $blog->setThemeVersion($themeVersion);
        $this->em->persist($blog);
        $this->em->flush();
    }
}
