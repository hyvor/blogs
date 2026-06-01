<?php

namespace App\Api\Console\Resolver;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogEntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ConsoleApiAuthorizationListener $authorizationListener,
    ) {
    }

    /** @return iterable<mixed> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (count($argument->getAttributes(MapBlogEntity::class)) === 0) {
            return [];
        }

        $class = $argument->getType();
        if ($class === null) {
            return [];
        }

        $id = $request->attributes->get('id');
        $blog = $this->authorizationListener->getBlog();

        /** @var class-string $class */
        $entity = $this->em->find($class, $id);

        if ($entity === null) {
            throw new NotFoundHttpException('Entity not found');
        }

        assert(method_exists($entity, 'getBlog'));

        $entityBlog = $entity->getBlog();
        assert($entityBlog instanceof Blog);

        if ($entityBlog->getId() !== $blog->getId()) {
            throw new NotFoundHttpException('Entity does not belong to blog');
        }

        yield $entity;
    }
}
