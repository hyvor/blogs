<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

/**
 * Rewrites Hyvor's known Cloudinary URLs (filler images and dream-big.mp3) to media.hyvor.com
 *
 * Only the known URLs are replaced. Any other Cloudinary URL is left as is and reported by getUnknown()
 */
class CloudinaryUpdater implements UpdaterInterface
{

    private const string CLOUDINARY_HOST = 'res.cloudinary.com';

    private const string OLD_FILLER_BASE = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images';
    private const string NEW_FILLER_BASE = 'https://media.hyvor.com/blogs.hyvor.com/filler-images';

    private const string OLD_DREAM_BIG = 'https://res.cloudinary.com/dqabfne6s/video/upload/v1726698292/blogs.hyvor.com/dream-big.mp3';
    private const string NEW_DREAM_BIG = 'https://media.hyvor.com/blogs.hyvor.com/dream-big.mp3';

    /**
     * @var array<string, string> old URL => new URL
     */
    private array $known;

    /**
     * @var array<string, string> old URL => new URL, for debugging
     */
    private array $matched = [];

    /**
     * @var array<string, true> Cloudinary URLs that are not known
     */
    private array $unknown = [];

    public function __construct()
    {
        $known = [self::OLD_DREAM_BIG => self::NEW_DREAM_BIG];

        for ($i = 1; $i <= 20; $i++) {
            $path = "/post-featured-images/$i.webp";
            $known[self::OLD_FILLER_BASE . $path] = self::NEW_FILLER_BASE . $path;
        }

        for ($i = 1; $i <= 5; $i++) {
            $path = "/author-images/$i.webp";
            $known[self::OLD_FILLER_BASE . $path] = self::NEW_FILLER_BASE . $path;
        }

        $this->known = $known;
    }

    public function update(string $url): false|string
    {
        if (isset($this->known[$url])) {
            return $this->matched[$url] = $this->known[$url];
        }

        if (str_contains($url, self::CLOUDINARY_HOST)) {
            $this->unknown[$url] = true;
        }

        return false;
    }

    public function getUpdatedCount(): int
    {
        return count($this->matched);
    }

    /**
     * @return array<string, string> all matched URLs (old => new)
     */
    public function getMatched(): array
    {
        return $this->matched;
    }

    /**
     * @return list<string> Cloudinary URLs found that are not in the known list (left unchanged)
     */
    public function getUnknown(): array
    {
        return array_keys($this->unknown);
    }

}
