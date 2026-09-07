<?php

namespace App\Service\Media;

use App\Entity\Blog;
use App\Entity\Media;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlLock;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessage;
use App\Service\Limit;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Media\Event\MediaNameUpdatedEvent;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\UnicodeString;
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
        private PermalinkService $permalinkService,
        private UpdateBlogUrlLock $updateBlogUrlLock,
        private MessageBusInterface $bus
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

    public function getMediaById(int $id): ?Media
    {
        return $this->em->getRepository(Media::class)->find($id);
    }

    public function getMediaByBlogAndName(Blog $blog, string $name): ?Media
    {
        return $this->em->getRepository(Media::class)->findOneBy([
            'blog' => $blog,
            'name' => $name,
        ]);
    }

    public function getContents(Media $media): ?string
    {
        try {
            return $this->filesystem->read($this->getPath($media->getBlog()->getId(), $media->getName()));
        } catch (FilesystemException) {
            return null;
        }
    }

    /**
     * @return resource|null
     */
    public function getContentsStream(Media $media)
    {
        try {
            return $this->filesystem->readStream($this->getPath($media->getBlog()->getId(), $media->getName()));
        } catch (FilesystemException) {
            return null;
        }
    }

    /** @throws MediaException */
    public function uploadFile(
        Blog $blog,
        UploadedFile $file,
        ?int $postId = null,
        ?string $fileName = null
    ): Media
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
            throw new MediaException('Error while uploading from storage');
        }

        try {
            $this->filesystem->writeStream($this->getPath($blog->getId(), $fileName), $stream);
        } catch (FilesystemException $e) {
            throw new MediaException('Error while uploading: ' . $e->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $this->createMedia(
            $blog,
            $postId,
            $fileName,
            (int)$file->getSize(),
            $file->getClientOriginalName(),
            $this->extensionFromName($fileName)
        );
    }

    /** @throws MediaException */
    public function uploadFromLocalPath(Blog $blog, string $localPath, ?int $postId = null): Media
    {
        if (!is_file($localPath)) {
            throw new MediaException("Local file not found: {$localPath}");
        }

        $stream = fopen($localPath, 'r');
        if ($stream === false) {
            throw new MediaException("Error while reading local file: {$localPath}");
        }

        $extension = $this->extensionFromName($localPath);
        $fileName = bin2hex(random_bytes(16)) . ($extension !== null ? '.' . $extension : '');
        $fileName = $this->getUniqueFilename($blog->getId(), $fileName);

        try {
            $this->filesystem->writeStream($this->getPath($blog->getId(), $fileName), $stream);
        } catch (FilesystemException $e) {
            throw new MediaException('Error while uploading: ' . $e->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $size = filesize($localPath);

        return $this->createMedia(
            $blog,
            $postId,
            $fileName,
            $size !== false ? $size : 0,
            basename($localPath),
            $extension
        );
    }

    /** @throws MediaException */
    public function uploadFromUrl(Blog $blog, string $url, ?int $postId = null): Media
    {
        try {
            $response = $this->httpClient->request('GET', $url, ['timeout' => 10]);
            $content = $response->getContent();
        } catch (HttpClientExceptionInterface) {
            throw new MediaException('Error while fetching image file');
        }

        if ($content === '') {
            throw new MediaException('Error while fetching image file');
        }

        if (strlen($content) > Limit::MAX_MEDIA_UPLOAD_SIZE) {
            throw new MediaException('File size is too large');
        }

        $extension = $this->extensionFromName((string)parse_url($url, PHP_URL_PATH));
        $fileName = bin2hex(random_bytes(16)) . ($extension !== null ? '.' . $extension : '');
        $fileName = $this->getUniqueFilename($blog->getId(), $fileName);

        try {
            $this->filesystem->write($this->getPath($blog->getId(), $fileName), $content);
        } catch (FilesystemException $e) {
            throw new MediaException('Error while uploading: ' . $e->getMessage());
        }

        return $this->createMedia(
            $blog,
            $postId,
            $fileName,
            strlen($content),
            $fileName,
            $extension
        );
    }

    private function createMedia(
        Blog $blog,
        ?int $postId,
        string $fileName,
        int $size,
        string $originalName,
        ?string $extension
    ): Media
    {
        $media = new Media();
        $media->setBlog($blog);
        $media->setPostId($postId);
        $media->setName($fileName);
        $media->setSize($size);
        $media->setOriginalName($originalName);
        $media->setExtension($extension);
        $media->setCreatedAt($this->now());
        $media->setUpdatedAt($this->now());

        $this->em->persist($media);
        $this->em->flush();

        $this->ed->dispatch(new MediaCreatedEvent($media));

        return $media;
    }


    /** @throws MediaException */
    public function updateName(Media $media, string $name): Media
    {
        $blog = $media->getBlog();
        $blogId = $blog->getId();
        $fileName = $this->toKebabCase($name);
        $fileName = $this->getUniqueFilename($blogId, $fileName);

        $oldPath = $this->getPath($blogId, $media->getName());
        $newPath = $this->getPath($blogId, $fileName);

        $oldUrl = $this->permalinkService->getMediaPermalink($media, $blog);

        $media->setName($fileName);
        $media->setExtension($this->extensionFromName($fileName));

        $newUrl = $this->permalinkService->getMediaPermalink($media, $blog);

        $canUpdateBlogUrls = $this->updateBlogUrlLock->canUpdate(UpdateBlogUrlEvent::MEDIA_URL_CHANGED, $media->getId());
        if ($canUpdateBlogUrls !== true) {
            throw new MediaException($canUpdateBlogUrls->getMessage());
        }

        $globalLock = $this->updateBlogUrlLock->mediaUrlGlobalLock();
        $mediaLock = $this->updateBlogUrlLock->mediaUrlLock($media->getId());

        $globalLock[0]->acquire(); // doesn't matter if we can't acquire, we just want to block HOSTING_CHANGED from running while we update the media URL
        if (!$mediaLock[0]->acquire()) {
            $globalLock[0]->release();
            throw new MediaException('Cannot update media URL because another process is already updating the media URL. Please try again later.');
        }

        $updateBlogUrlsMessage = new UpdateBlogUrlsMessage(
            blogId: $blogId,
            event: UpdateBlogUrlEvent::MEDIA_URL_CHANGED,
            lockKeys: [
                $globalLock[1],
                $mediaLock[1]
            ],
            mediaId: $media->getId(),
            mediaOldUrl: $oldUrl,
            mediaNewUrl: $newUrl
        );

        try {
            $this->filesystem->move($oldPath, $newPath);
        } catch (FilesystemException $e) {
            throw new MediaException('Error while renaming: ' . $e->getMessage());
        }

        $this->em->flush();
        $this->bus->dispatch($updateBlogUrlsMessage);
        $this->ed->dispatch(new MediaNameUpdatedEvent($media, $oldUrl, $newUrl));

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
        $extension = $this->extensionFromName($name);
        $nameWithoutExtension = $extension !== null ? substr($name, 0, -strlen($extension) - 1) : $name;
        return new UnicodeString($nameWithoutExtension)->kebab() . ($extension !== null ? '.' . $extension : '');
    }

    private function extensionFromName(string $name): ?string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        return $extension !== '' ? $extension : null;
    }
}
