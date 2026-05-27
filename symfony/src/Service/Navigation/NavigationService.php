<?php

namespace App\Service\Navigation;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Entity\NavigationVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class NavigationService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    /** @return Navigation[] */
    public function getNavigations(Blog $blog): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('n')
            ->addSelect('v')
            ->from(Navigation::class, 'n')
            ->leftJoin('n.variants', 'v')
            ->where('n.blog_id = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('n.sort', 'ASC');

        /** @var Navigation[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getNavigationCount(Blog $blog, string $type): int
    {
        return $this->em->getRepository(Navigation::class)->count([
            'blog_id' => $blog->getId(),
            'type' => $type,
        ]);
    }

    public function createNavigation(
        Blog $blog,
        string $url,
        string $type,
        Language $primaryLanguage,
        string $name,
    ): Navigation {
        $now = $this->now();
        $navigation = new Navigation();
        $navigation->setBlog($blog);
        $navigation->setBlogId($blog->getId());
        $navigation->setUrl($url);
        $navigation->setType($type);
        $navigation->setSort(0);
        $navigation->setCreatedAt($now);
        $navigation->setUpdatedAt($now);
        $this->em->persist($navigation);
        $this->em->flush(); // flush first to get ID

        $variant = new NavigationVariant();
        $variant->setNavigation($navigation);
        $variant->setNavigationId($navigation->getId());
        $variant->setLanguage($primaryLanguage);
        $variant->setLanguageId($primaryLanguage->getId());
        $variant->setName($name);
        $variant->setCreatedAt($now);
        $variant->setUpdatedAt($now);
        $this->em->persist($variant);
        $navigation->getVariants()->add($variant);
        $this->em->flush();

        return $navigation;
    }

    public function updateNavigation(Navigation $navigation, string $url, string $type): Navigation
    {
        $navigation->setUrl($url);
        $navigation->setType($type);
        $navigation->setUpdatedAt($this->now());
        $this->em->flush();
        return $navigation;
    }

    public function deleteNavigation(Navigation $navigation): void
    {
        // delete variants first
        foreach ($navigation->getVariants() as $variant) {
            $this->em->remove($variant);
        }
        $this->em->flush();
        $this->em->remove($navigation);
        $this->em->flush();
    }

    /** @param int[] $ids */
    public function updateSort(Blog $blog, array $ids): void
    {
        foreach ($ids as $sort => $id) {
            $navigation = $this->em->getRepository(Navigation::class)->findOneBy([
                'id' => $id,
                'blog_id' => $blog->getId(),
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
    ): NavigationVariant {
        $now = $this->now();
        $variant = new NavigationVariant();
        $variant->setNavigation($navigation);
        $variant->setNavigationId($navigation->getId());
        $variant->setLanguage($language);
        $variant->setLanguageId($language->getId());
        $variant->setName($name);
        $variant->setCreatedAt($now);
        $variant->setUpdatedAt($now);
        $this->em->persist($variant);
        $this->em->flush();
        return $variant;
    }

    public function getNavigationVariant(Navigation $navigation, Language $language): ?NavigationVariant
    {
        return $this->em->getRepository(NavigationVariant::class)->findOneBy([
            'navigation_id' => $navigation->getId(),
            'language_id' => $language->getId(),
        ]);
    }

    public function updateNavigationVariant(NavigationVariant $variant, string $name): NavigationVariant
    {
        $variant->setName($name);
        $variant->setUpdatedAt($this->now());
        $this->em->flush();
        return $variant;
    }

    public function deleteNavigationVariant(NavigationVariant $variant): void
    {
        $this->em->remove($variant);
        $this->em->flush();
    }
}
