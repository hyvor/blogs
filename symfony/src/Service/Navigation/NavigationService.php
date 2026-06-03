<?php

namespace App\Service\Navigation;

use App\Entity\Blog;
use App\Entity\Enum\NavigationType;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Entity\NavigationVariant;
use App\Service\Language\LanguageService;
use App\Service\Navigation\Event\NavigationChangedEvent;
use App\Service\Navigation\Event\NavigationVariantChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NavigationService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private LanguageService $languageService,
        private EventDispatcherInterface $ed,
    ) {}

    /** @return Navigation[] */
    public function getNavigations(Blog $blog): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('n')
            ->addSelect('v')
            ->from(Navigation::class, 'n')
            ->leftJoin('n.variants', 'v')
            ->where('n.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('n.sort', 'ASC');

        /** @var Navigation[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getNavigationCount(Blog $blog, NavigationType $type): int
    {
        return $this->em->getRepository(Navigation::class)->count([
            'blog' => $blog,
            'type' => $type,
        ]);
    }

    public function createNavigation(
        Blog $blog,
        string $url,
        NavigationType $type,
        string $name,
    ): Navigation {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        $now = $this->now();
        $navigation = new Navigation();
        $navigation->setBlog($blog);
        $navigation->setUrl($url);
        $navigation->setType($type);
        $navigation->setSort(0);
        $navigation->setCreatedAt($now);
        $navigation->setUpdatedAt($now);
        $this->em->persist($navigation);

        $this->createNavigationVariant($navigation, $primaryLanguage, $name, flush: false);

        $this->em->flush();
        $this->ed->dispatch(new NavigationChangedEvent($navigation));

        return $navigation;
    }

    public function updateNavigation(Navigation $navigation, string $url, NavigationType $type): Navigation
    {
        $navigation->setUrl($url);
        $navigation->setType($type);
        $navigation->setUpdatedAt($this->now());
        $this->em->flush();
        $this->ed->dispatch(new NavigationChangedEvent($navigation));
        return $navigation;
    }

    public function deleteNavigation(Navigation $navigation): void
    {
        foreach ($navigation->getVariants() as $variant) {
            $this->em->remove($variant);
        }
        $this->em->flush();
        $this->em->remove($navigation);
        $this->em->flush();
        $this->ed->dispatch(new NavigationChangedEvent($navigation));
    }

    /** @param int[] $ids */
    public function updateSort(Blog $blog, array $ids): void
    {
        foreach ($ids as $sort => $id) {
            $navigation = $this->em->getRepository(Navigation::class)->findOneBy([
                'id' => $id,
                'blog' => $blog,
            ]);
            if ($navigation !== null) {
                $navigation->setSort($sort);
            }
        }
        $this->em->flush();
    }

    public function createNavigationVariant(
        Navigation $navigation,
        Language $language,
        ?string $name,
        bool $flush = true,
    ): NavigationVariant {
        $now = $this->now();
        $variant = new NavigationVariant();
        $variant->setNavigation($navigation);
        $variant->setLanguage($language);
        $variant->setLanguageId($language->getId());
        $variant->setName($name);
        $variant->setCreatedAt($now);
        $variant->setUpdatedAt($now);

        $navigation->addVariant($variant);

        $this->em->persist($variant);
        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new NavigationVariantChangedEvent($variant));
        }

        return $variant;
    }

    public function getNavigationVariant(Navigation $navigation, Language $language): ?NavigationVariant
    {
        return $this->em->getRepository(NavigationVariant::class)->findOneBy([
            'navigation' => $navigation,
            'language_id' => $language->getId(),
        ]);
    }

    public function updateNavigationVariant(NavigationVariant $variant, string $name): NavigationVariant
    {
        $variant->setName($name);
        $variant->setUpdatedAt($this->now());
        $this->em->flush();
        $this->ed->dispatch(new NavigationVariantChangedEvent($variant));
        return $variant;
    }

    public function deleteNavigationVariant(NavigationVariant $variant): void
    {
        $this->em->remove($variant);
        $this->em->flush();
        $this->ed->dispatch(new NavigationVariantChangedEvent($variant));
    }
}
