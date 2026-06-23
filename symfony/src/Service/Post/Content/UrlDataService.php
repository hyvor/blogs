<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\UrlData;
use Doctrine\ORM\EntityManagerInterface;

class UrlDataService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @return array<string, mixed>|null
     */
    public function getEmbed(string $url): ?array
    {
        $urlData = $this->em->getRepository(UrlData::class)->findOneBy([
            'url' => $url,
            'fetch_type' => 'embed',
        ]);

        if ($urlData === null) {
            return null;
        }

        return [
            'embed' => $urlData->getHtml(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLink(string $url): ?array
    {
        $urlData = $this->em->getRepository(UrlData::class)->findOneBy([
            'url' => $url,
            'fetch_type' => 'link',
        ]);

        if ($urlData === null) {
            return null;
        }

        $finalUrl = $urlData->getFinalUrl() ?? $url;
        $domain = parse_url($finalUrl, PHP_URL_HOST);

        return [
            'url' => $finalUrl,
            'original_url' => $url,
            'title' => $urlData->getTitle(),
            'description' => $urlData->getDescription(),
            'thumbnail_url' => $urlData->getThumbnailUrl(),
            'icon_url' => $urlData->getIconUrl(),
            'site' => $urlData->getSite(),
            'domain' => is_string($domain) ? $domain : '',
        ];
    }
}
