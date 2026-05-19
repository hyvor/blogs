<?php

namespace App\Service\Language;

use App\Entity\Blog;
use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

    /** @return Language[] */
    public function getAllLanguages(Blog $blog): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('l')
            ->from(Language::class, 'l')
            ->where('l.blog_id = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('l.is_primary', 'DESC')
            ->addOrderBy('l.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getLanguageByCode(Blog $blog, string $code): ?Language
    {
        return $this->em->getRepository(Language::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'code' => $code,
        ]);
    }

    public function getLanguageById(Blog $blog, int $id): ?Language
    {
        return $this->em->getRepository(Language::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'id' => $id,
        ]);
    }

    public function getPrimaryLanguage(Blog $blog): Language
    {
        $language = $this->em->getRepository(Language::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'is_primary' => true,
        ]);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('No primary language found');
        }
        return $language;
    }

    public function updateLanguage(Language $language, string $code, string $name, string $direction): Language
    {
        $language->setCode($code);
        $language->setName($name);
        $language->setDirection($direction);
        $language->setUpdatedAt($this->now());
        $this->em->flush();
        return $language;
    }

    public function deleteLanguage(Language $language): void
    {
        $this->em->remove($language);
        $this->em->flush();
    }
}
