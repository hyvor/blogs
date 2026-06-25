<?php

namespace App\Service\Import;

use App\Entity\Blog;
use App\Entity\Enum\ImportType;
use App\Entity\Enum\JobStatus;
use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class ImportService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return Import[]
     */
    public function getImports(Blog $blog): array
    {
        /** @var Import[] */
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from(Import::class, 'i')
            ->where('i.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createImport(
        Blog $blog,
        ImportType $type,
        string $name,
        array $options = [],
    ): Import {
        $import = new Import();
        $import->setBlog($blog);
        $import->setType($type);
        $import->setName($name);
        $import->setStatus(JobStatus::PENDING);
        $import->setOptions($options);
        $import->setCreatedAt($this->now());
        $import->setUpdatedAt($this->now());

        $this->em->persist($import);
        $this->em->flush();

        return $import;
    }

    public function hasPendingImports(Blog $blog): bool
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Import::class, 'i')
            ->where('i.blog = :blog')
            ->andWhere('i.status = :status')
            ->setParameter('blog', $blog)
            ->setParameter('status', JobStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function markCompleted(Import $import, int $postsCount): void
    {
        $import->setStatus(JobStatus::COMPLETED);
        $import->setPostsCount($postsCount);
        $import->setUpdatedAt($this->now());
        $this->em->flush();
    }

    public function markFailed(Import $import, string $error): void
    {
        $import->setStatus(JobStatus::FAILED);
        $import->setError($error);
        $import->setUpdatedAt($this->now());
        $this->em->flush();
    }
}
