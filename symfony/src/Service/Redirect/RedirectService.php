<?php

namespace App\Service\Redirect;

use App\Entity\Blog;
use App\Entity\Enum\RedirectType;
use App\Entity\Redirect;
use App\Service\Redirect\Event\RedirectChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RedirectService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $ed,
    ) {
    }

    /**
     * @return Redirect[]
     */
    public function getRedirects(Blog $blog, string $search, int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(Redirect::class, 'r')
            ->where('r.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('r.dynamic', 'DESC')
            ->addOrderBy('r.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($search !== '') {
            $qb->andWhere('r.path LIKE :search OR r.to LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        /** @var Redirect[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getRedirectsCount(Blog $blog): int
    {
        return $this->em->getRepository(Redirect::class)->count(['blog' => $blog]);
    }

    public function getDynamicRedirectCount(Blog $blog): int
    {
        return $this->em->getRepository(Redirect::class)->count([
            'blog' => $blog,
            'dynamic' => true,
        ]);
    }

    public function hasRedirectForPath(Blog $blog, string $path): bool
    {
        return $this->em->getRepository(Redirect::class)->findOneBy([
            'blog' => $blog,
            'path' => $path,
        ]) !== null;
    }

    /**
     * gets the regex pattern from user's "path" input.
     */
    private function getRegex(string $userRegex): string
    {
        return ('~' . $userRegex . '~');
    }

    public function validateRegex(string $regex): bool
    {
        return @preg_match($this->getRegex($regex), '') !== false;
    }

    public function getRedirectByPath(Blog $blog, string $path): ?Redirect
    {
        return $this->em->getRepository(Redirect::class)->findOneBy([
            'blog' => $blog,
            'path' => $path,
            'dynamic' => false,
        ]);
    }

    /**
     * @param object[] $events
     */
    public function createRedirect(
        Blog $blog,
        bool $dynamic,
        string $path,
        string $to,
        RedirectType $type,
        bool $flush = true,
        array &$events = []
    ): Redirect {
        $now = $this->now();

        $redirect = new Redirect();
        $redirect->setCreatedAt($now);
        $redirect->setUpdatedAt($now);
        $redirect->setBlog($blog);
        $redirect->setDynamic($dynamic);
        $redirect->setPath($path);
        $redirect->setTo($to);
        $redirect->setType($type);

        $this->em->persist($redirect);

        $event = new RedirectChangedEvent($redirect);
        $events[] = $event;

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch($event);
        }

        return $redirect;
    }

    /**
     * @param array{path?: string, to?: string, type?: RedirectType} $updates
     * @param object[] $events
     */
    public function updateRedirect(
        Redirect $redirect,
        array $updates,
        bool $flush = true,
        array &$events = []
    ): Redirect
    {
        $oldRedirect = clone $redirect;

        if (array_key_exists('path', $updates)) {
            $redirect->setPath($updates['path']);
        }
        if (array_key_exists('to', $updates)) {
            $redirect->setTo($updates['to']);
        }
        if (array_key_exists('type', $updates)) {
            $redirect->setType($updates['type']);
        }
        $redirect->setUpdatedAt($this->now());

        $event = new RedirectChangedEvent($redirect, $oldRedirect);
        $events[] = $event;

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch($event);
        }

        return $redirect;
    }

    public function deleteRedirect(Redirect $redirect): void
    {
        $this->em->remove($redirect);
        $this->em->flush();
        $this->ed->dispatch(new RedirectChangedEvent($redirect));
    }

    /**
     * @return array{to: string, type: RedirectType}|null
     */
    public function findRedirectForPath(Blog $blog, string $path): ?array
    {
        $dynamicRedirects = $this->em->getRepository(Redirect::class)->findBy([
            'blog' => $blog,
            'dynamic' => true,
        ]);

        foreach ($dynamicRedirects as $redirect) {
            $regex = $this->getRegex($redirect->getPath());
            if (@preg_match($regex, $path)) {
                $dynamicTo = preg_replace($regex, $redirect->getTo(), $path);
                if ($dynamicTo !== null) {
                    return ['to' => $dynamicTo, 'type' => $redirect->getType()];
                }
            }
        }

        $staticRedirect = $this->getRedirectByPath($blog, $path);

        if ($staticRedirect) {
            return ['to' => $staticRedirect->getTo(), 'type' => $staticRedirect->getType()];
        }

        return null;
    }
}
