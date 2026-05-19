<?php

namespace App\Service\Redirect;

use App\Entity\Blog;
use App\Entity\Redirect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @return Redirect[]
     */
    public function getRedirects(Blog $blog, string $search, int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(Redirect::class, 'r')
            ->where('r.blog_id = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('r.dynamic', 'DESC')
            ->addOrderBy('r.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($search !== '') {
            $qb->andWhere('r.path LIKE :search OR r.to LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getRedirectsCount(Blog $blog): int
    {
        return $this->em->getRepository(Redirect::class)->count(['blog_id' => $blog->getId()]);
    }

    public function getDynamicRedirectCount(Blog $blog): int
    {
        return $this->em->getRepository(Redirect::class)->count([
            'blog_id' => $blog->getId(),
            'dynamic' => true,
        ]);
    }

    public function hasRedirectForPath(Blog $blog, string $path): bool
    {
        return $this->em->getRepository(Redirect::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'path' => $path,
        ]) !== null;
    }

    public function validateRegex(string $regex): bool
    {
        return @preg_match('/' . $regex . '/', '') !== false;
    }

    public function createRedirect(
        Blog $blog,
        bool $dynamic,
        string $path,
        string $to,
        string $type,
    ): Redirect {
        $now = $this->now();
        $redirect = new Redirect();
        $redirect->setBlog($blog);
        $redirect->setBlogId($blog->getId());
        $redirect->setDynamic($dynamic);
        $redirect->setPath($path);
        $redirect->setTo($to);
        $redirect->setType($type);
        $redirect->setCreatedAt($now);
        $redirect->setUpdatedAt($now);
        $this->em->persist($redirect);
        $this->em->flush();
        return $redirect;
    }

    public function updateRedirect(Redirect $redirect, ?string $path, ?string $to, ?string $type): Redirect
    {
        if ($path !== null) {
            $redirect->setPath($path);
        }
        if ($to !== null) {
            $redirect->setTo($to);
        }
        if ($type !== null) {
            $redirect->setType($type);
        }
        $redirect->setUpdatedAt($this->now());
        $this->em->flush();
        return $redirect;
    }

    public function deleteRedirect(Redirect $redirect): void
    {
        $this->em->remove($redirect);
        $this->em->flush();
    }

    public function getRedirectByIdAndBlog(int $id, Blog $blog): Redirect
    {
        $redirect = $this->em->getRepository(Redirect::class)->findOneBy([
            'id' => $id,
            'blog_id' => $blog->getId(),
        ]);
        if ($redirect === null) {
            throw new NotFoundHttpException('Redirect not found');
        }
        return $redirect;
    }
}
