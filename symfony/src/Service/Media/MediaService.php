<?php

namespace App\Service\Media;

use App\Entity\Blog;
use App\Entity\Media;
use App\Service\Limit;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MediaService
{
    use ClockAwareTrait;

    public const array IMAGE_EXTENSIONS = [
        'png',
        'jpg',
        'jpeg',
        'jfif',
        'pjpeg',
        'pjp',
        'gif',
        'apng',
        'avif',
        'svg',
        'webp',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $filesystem,
        private EventDispatcherInterface $ed,
        private HttpClientInterface $httpClient,
    ) {}

    /**
     * @param string[]|null $extensions
     * @return Media[]
     */
    public function getMedia(
        Blog $blog,
        int $limit = 0,
        int $offset = 0,
        ?array $extensions = null,
        ?string $search = null,
    ): array {
        $qb = $this->em->createQueryBuilder()
            ->select('m')
            ->from(Media::class, 'm')
            ->where('m.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult($offset);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        if ($extensions !== null && count($extensions) > 0) {
            $qb->andWhere('m.extension IN (:extensions)')
                ->setParameter('extensions', $extensions);
        }

        if ($search !== null && $search !== '') {
            $qb->andWhere('(m.name LIKE :search OR m.original_name LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        /** @var Media[] */
        return $qb->getQuery()->getResult();
    }

    /** @throws MediaUploadException */
    public function uploadFile(Blog $blog, UploadedFile $file, ?int $postId = null, ?string $fileName = null): Media
    {
        $extension = $file->getClientOriginalExtension();

        if ($fileName === null) {
            $fileName = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
        } else {
            $fileName = $this->toKebabCase($fileName);
        }

        $fileName = $this->getUniqueFilename($blog->getId(), $fileName);

        $stream = fopen($file->getPathname(), 'r');
        if ($stream === false) {
            throw new MediaUploadException('Error while uploading from storage');
        }

        try {
            $this->filesystem->writeStream($this->getPath($blog->getId(), $fileName), $stream);
        } catch (FilesystemException $e) {
            throw new MediaUploadException('Error while uploading: ' . $e->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $media = new Media();
        $media->setBlog($blog);
        $media->setPostId($postId);
        $media->setName($fileName);
        $media->setSize((int)$file->getSize());
        $media->setOriginalName((string)$file->getClientOriginalName());
        $media->setExtension($this->extensionFromName($fileName));
        $media->setCreatedAt($this->now());

        $this->em->persist($media);
        $this->em->flush();

        $this->ed->dispatch(new MediaCreatedEvent($media));

        return $media;
    }

    /** @throws MediaUploadException */
    public function uploadFromUrl(Blog $blog, string $url, ?int $postId = null): Media
    {
        try {
            $response = $this->httpClient->request('GET', $url, ['timeout' => 10]);
            $content = $response->getContent();
        } catch (HttpClientExceptionInterface) {
            throw new MediaUploadException('Error while fetching image file');
        }

        if ($content === '') {
            throw new MediaUploadException('Error while fetching image file');
        }

        if (strlen($content) > Limit::MAX_MEDIA_UPLOAD_SIZE) {
            throw new MediaUploadException('File size is too large');
        }

        $extension = $this->extensionFromName((string)parse_url($url, PHP_URL_PATH));
        $fileName = bin2hex(random_bytes(16)) . ($extension !== null ? '.' . $extension : '');
        $fileName = $this->getUniqueFilename($blog->getId(), $fileName);

        try {
            $this->filesystem->write($this->getPath($blog->getId(), $fileName), $content);
        } catch (FilesystemException $e) {
            throw new MediaUploadException('Error while uploading: ' . $e->getMessage());
        }

        $media = new Media();
        $media->setBlog($blog);
        $media->setPostId($postId);
        $media->setName($fileName);
        $media->setSize(strlen($content));
        $media->setOriginalName($fileName);
        $media->setExtension($extension);
        $media->setCreatedAt($this->now());

        $this->em->persist($media);
        $this->em->flush();

        $this->ed->dispatch(new MediaCreatedEvent($media));

        return $media;
    }

    public function updateName(Media $media, string $name): Media
    {
        $blogId = $media->getBlog()->getId();
        $fileName = $this->toKebabCase($name);
        $fileName = $this->getUniqueFilename($blogId, $fileName);

        $oldPath = $this->getPath($blogId, $media->getName());
        $newPath = $this->getPath($blogId, $fileName);

        $media->setName($fileName);
        $media->setExtension($this->extensionFromName($fileName));
        $this->em->flush();

        try {
            $this->filesystem->move($oldPath, $newPath);
        } catch (FilesystemException $e) {
            throw new MediaUploadException('Error while renaming: ' . $e->getMessage());
        }

        return $media;
    }

    public function deleteMedia(Media $media): void
    {
        try {
            $this->filesystem->delete($this->getPath($media->getBlog()->getId(), $media->getName()));
        } catch (FilesystemException) {
            // ignore, the DB record is the source of truth
        }

        $this->em->remove($media);
        $this->em->flush();

        $this->ed->dispatch(new MediaDeletedEvent($media));
    }

    private function getUniqueFilename(int $blogId, string $name): string
    {
        $fileName = $name;

        $start = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $i = 1;
        while ($this->filesystem->fileExists($this->getPath($blogId, $fileName))) {
            $fileName = $start . '-' . $i . ($ext !== '' ? '.' . $ext : '');
            $i++;
        }

        return $fileName;
    }

    private function getPath(int $blogId, string $fileName): string
    {
        return 'blog/' . $blogId . '/' . $fileName;
    }

    private function toKebabCase(string $name): string
    {
        return preg_replace('/\s+/u', '-', trim(mb_strtolower($name))) ?? $name;
    }

    private function extensionFromName(string $name): ?string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        return $extension !== '' ? $extension : null;
    }
}
