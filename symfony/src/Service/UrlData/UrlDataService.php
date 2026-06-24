<?php declare(strict_types=1);

namespace App\Service\UrlData;

use App\Entity\UrlData;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Unfold\Exception\UnfoldException;
use Hyvor\Unfold\Unfold;
use Symfony\Component\Clock\ClockAwareTrait;

class UrlDataService
{

    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array<string, mixed>|null
     * @throws UnfoldException
     */
    public function getEmbed(string $url, bool $force = false): string
    {
        $urlData = $this->em->getRepository(UrlData::class)->findOneBy([
            'url' => $url,
            'fetch_type' => 'embed',
        ]);

        if ($urlData !== null && !$force) {
            return $urlData->getHtml() ?? '';
        }

        $embed = Unfold::embed($url);

        $urlData = new UrlData();
        $urlData->setCreatedAt($this->now());
        $urlData->setUpdatedAt($this->now());
        $urlData->setUrl($url);
        $urlData->setFetchType('embed');
        $urlData->setResult('ok');
        $urlData->setHtml($embed->embed);

        $this->em->persist($urlData);
        $this->em->flush();

        return $embed->embed;
    }

    /**
     * @return array{url: string, final_url: string, title: string, description: string, thumbnail_url: string, icon_url: string, site_url: string}
     * @throws UnfoldException
     */
    public function getLink(string $url, bool $force = false): array
    {
        $urlData = $this->em->getRepository(UrlData::class)->findOneBy([
            'url' => $url,
            'fetch_type' => 'link',
        ]);

        if ($urlData === null || $force) {
            $link = Unfold::link($url);

            $urlData = new UrlData();
            $urlData->setCreatedAt($this->now());
            $urlData->setUpdatedAt($this->now());
            $urlData->setUrl($url);
            $urlData->setFinalUrl($link->lastUrl);
            $urlData->setFetchType('link');
            $urlData->setResult('ok');
            $urlData->setTitle($link->title);
            $urlData->setDescription($link->description);
            $urlData->setThumbnailUrl($link->thumbnailUrl);
            $urlData->setIconUrl($link->iconUrl);
            $urlData->setSite($link->siteUrl);

            $this->em->persist($urlData);
            $this->em->flush();
        }

        return [
            'url' => $urlData->getUrl(),
            'final_url' => $urlData->getFinalUrl(),
            'title' => $urlData->getTitle(),
            'description' => $urlData->getDescription(),
            'thumbnail_url' => $urlData->getThumbnailUrl(),
            'icon_url' => $urlData->getIconUrl(),
            'site_url' => $urlData->getSite(),
        ];
    }
}
