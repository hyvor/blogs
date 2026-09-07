<?php

namespace App\Service\Export;

use App\Entity\Blog;
use App\Entity\Enum\ExportFormat;
use App\Entity\Enum\JobStatus;
use App\Entity\Export;
use App\Service\AppConfig;
use App\Service\Export\Message\ExportMessage;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class ExportService
{
    use ClockAwareTrait;

    private const int RECENT_LIMIT = 25;

    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private Filesystem $filesystem,
        private AppConfig $appConfig,
        private HyvorBlogsExporter $hyvorBlogsExporter,
    ) {
    }

    public function hasPendingExports(Blog $blog): bool
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Export::class, 'e')
            ->where('e.blog = :blog')
            ->andWhere('e.status = :status')
            ->setParameter('blog', $blog)
            ->setParameter('status', JobStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function createExport(Blog $blog, ExportFormat $format): Export
    {
        $export = new Export();
        $export->setBlog($blog);
        $export->setFormat($format);
        $export->setStatus(JobStatus::PENDING);
        $export->setCreatedAt($this->now());
        $export->setUpdatedAt($this->now());

        $this->em->persist($export);
        $this->em->flush();

        $this->bus->dispatch(new ExportMessage($export->getId()));

        return $export;
    }

    /**
     * @return Export[]
     */
    public function getExports(Blog $blog): array
    {
        /** @var Export[] */
        return $this->em->createQueryBuilder()
            ->select('e')
            ->from(Export::class, 'e')
            ->where('e.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(self::RECENT_LIMIT)
            ->getQuery()
            ->getResult();
    }

    public function runExport(Export $export): void
    {
        $blog = $export->getBlog();

        $exporter = match ($export->getFormat()) {
            ExportFormat::HYVOR_BLOGS => $this->hyvorBlogsExporter,
            default => null,
        };

        if ($exporter === null) {
            $this->fail($export, 'Invalid format');
            return;
        }

        $localPath = $exporter->createFile($blog);
        $path = 'exports/' . $blog->getId() . '/' . date('Y-m-d') . '-' . $export->getId() . '.json';

        try {
            $stream = fopen($localPath, 'r');
            if ($stream === false) {
                throw new \RuntimeException("Unable to open exported file: {$localPath}");
            }
            $this->filesystem->writeStream($path, $stream);
        } catch (FilesystemException $e) {
            $this->fail($export, 'Failed to upload file to storage: ' . $e->getMessage());
            return;
        } finally {
            unlink($localPath);
        }

        $url = $this->appConfig->getTlsMode()->getScheme() . '://' . $this->appConfig->getDomainApp() . '/api/media/' . $path;

        $export->setStatus(JobStatus::COMPLETED);
        $export->setUrl($url);
        $export->setUpdatedAt($this->now());
        $this->em->flush();
    }

    private function fail(Export $export, string $error): void
    {
        $export->setStatus(JobStatus::FAILED);
        $export->setError($error);
        $export->setUpdatedAt($this->now());
        $this->em->flush();
    }
}
