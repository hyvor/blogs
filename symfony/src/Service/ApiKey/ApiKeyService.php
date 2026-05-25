<?php

namespace App\Service\ApiKey;

use App\Entity\ApiKey;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiKeyService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    /** @return ApiKey[] */
    public function getApiKeys(Blog $blog): array
    {
        return $this->em->getRepository(ApiKey::class)->findBy(
            ['blog' => $blog],
        );
    }

    public function getApiKeysCount(Blog $blog): int
    {
        return $this->em->getRepository(ApiKey::class)->count(['blog_id' => $blog->getId()]);
    }

    public function createApiKey(Blog $blog, string $name, string $type): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setBlog($blog);
        $apiKey->setBlogId($blog->getId());
        $apiKey->setName($name);
        $apiKey->setType($type);
        $apiKey->setApiKey(bin2hex(random_bytes(16)));
        $now = $this->now();
        $apiKey->setCreatedAt($now);
        $apiKey->setUpdatedAt($now);
        $this->em->persist($apiKey);
        $this->em->flush();
        return $apiKey;
    }

    public function regenerateApiKey(ApiKey $apiKey): ApiKey
    {
        $apiKey->setApiKey(bin2hex(random_bytes(16)));
        $apiKey->setUpdatedAt($this->now());
        $this->em->flush();
        return $apiKey;
    }

    public function deleteApiKey(ApiKey $apiKey): void
    {
        $this->em->remove($apiKey);
        $this->em->flush();
    }

    public function getApiKeyByIdAndBlog(int $id, Blog $blog): ApiKey
    {
        $apiKey = $this->em->getRepository(ApiKey::class)->findOneBy([
            'id' => $id,
            'blog_id' => $blog->getId(),
        ]);
        if ($apiKey === null) {
            throw new NotFoundHttpException('API key not found');
        }
        return $apiKey;
    }

    public function getByRawKey(Blog $blog, string $rawKey): ?ApiKey
    {
        return $this->em->getRepository(ApiKey::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'api_key' => $rawKey,
            'type' => 'console',
        ]);
    }
}
