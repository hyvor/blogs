<?php

namespace App\Service\Language;

use App\Entity\Blog;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Language;
use App\Service\Language\Event\LanguageChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LanguageService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $ed,
    ) {}

    public function createLanguage(
        Blog $blog,
        string $code,
        string $name,
        LanguageDirection $direction = LanguageDirection::LTR,
        bool $isPrimary = false,
        bool $flush = true,
    ): Language {
        $language = new Language();
        $language->setBlog($blog);
        $language->setCode($code);
        $language->setName($name);
        $language->setDirection($direction);
        $language->setIsPrimary($isPrimary);
        $this->em->persist($language);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new LanguageChangedEvent($language));
        }

        $blog->getLanguages()->add($language);

        return $language;
    }

    /**
     * @return Language[] all languages of the blog, sorted with primary first, then by id
     */
    public function getAllLanguages(Blog $blog): array
    {
        $languages = $blog->getLanguages()->toArray();

        // sort primary first, id sorted
        usort($languages, function (Language $a, Language $b) {
            if ($a->isPrimary() && !$b->isPrimary()) {
                return -1;
            } elseif (!$a->isPrimary() && $b->isPrimary()) {
                return 1;
            } else {
                return $a->getId() <=> $b->getId();
            }
        });

        return $languages;
    }

    public function getLanguageByCode(Blog $blog, string $code): ?Language
    {
        return $this->em->getRepository(Language::class)->findOneBy([
            'blog' => $blog,
            'code' => $code,
        ]);
    }

    public function getLanguageById(Blog $blog, int $id): ?Language
    {
        return $this->em->getRepository(Language::class)->findOneBy([
            'blog' => $blog,
            'id' => $id,
        ]);
    }

    public function getPrimaryLanguage(Blog $blog): Language
    {
        $languages = $this->getAllLanguages($blog);

        foreach ($languages as $language) {
            if ($language->isPrimary()) {
                return $language;
            }
        }

        throw new \RuntimeException('No primary language found for blog ' . $blog->getId());
    }

    public function updateLanguage(Language $language, ?string $code, ?string $name, ?LanguageDirection $direction): Language
    {
        if ($code !== null) {
            $language->setCode($code);
        }
        if ($name !== null) {
            $language->setName($name);
        }
        if ($direction !== null) {
            $language->setDirection($direction);
        }
        $language->setUpdatedAt($this->now());
        $this->em->flush();
        $this->ed->dispatch(new LanguageChangedEvent($language));
        return $language;
    }

    public function deleteLanguage(Language $language): void
    {
        $this->em->remove($language);
        $this->em->flush();
        $this->ed->dispatch(new LanguageChangedEvent($language));
    }
}
