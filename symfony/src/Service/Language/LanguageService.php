<?php

namespace App\Service\Language;

use App\Entity\Blog;
use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class LanguageService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    public function createLanguage(
        Blog $blog,
        string $code,
        string $name,
        string $direction = 'ltr',
        bool $isPrimary = false,
    ): Language {
        $language = new Language();
        $language->setBlog($blog);
        $language->setBlogId($blog->getId());
        $language->setCode($code);
        $language->setName($name);
        $language->setDirection($direction);
        $language->setIsPrimary($isPrimary);
        $this->em->persist($language);
        $this->em->flush();
        return $language;
    }
}
