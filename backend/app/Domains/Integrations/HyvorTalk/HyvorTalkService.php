<?php declare(strict_types=1);

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\HyvorTalkWebsite;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InstanceUrl;
use Hyvor\Internal\InternalApi\InternalApi;

class HyvorTalkService
{

    /**
     * @param 'create-website'|'set-domains' $endpoint
     * @param array<string, mixed> $data
     * @return array<mixed>
     * @throws InternalApiCallFailedException
     */
    private static function callApi(string $endpoint, array $data)
    {
        return InternalApi::call(
            ComponentType::TALK,
            'POST',
            '/blogs/integration/' . $endpoint,
            $data,
        );
    }

    public static function getHyvorTalkWebsite(Blog $blog): ?HyvorTalkWebsite
    {
        return HyvorTalkWebsite::where('blog_id', $blog->id)->first();
    }

    public static function createHyvorTalkWebsite(Blog $blog): HyvorTalkWebsite
    {

        $domain = PermalinkRepository::getBlogDomain($blog);

        $data = self::callApi('create-website', [
            'name' => $blog->subdomain,
            'domain' => $domain,
            'hyvor_user_id' => $blog->hyvor_user_id,
        ]);

        return HyvorTalkWebsite::create([
            'blog_id' => $blog->id,
            'website_id' => intval($data['id']),
        ]);

    }

    public static function deleteHyvorTalkWebsite(HyvorTalkWebsite $website): void
    {
        $website->delete();
    }

    /**
     * @param string[] $domains
     */
    public static function updateDomains(HyvorTalkWebsite $website, array $domains): void
    {
        self::callApi('set-domains', [
            'website_id' => $website->website_id,
            'domains' => $domains,
        ]);
    }

    /**
     * @return mixed[]
     * @throws InternalApiCallFailedException
     */
    public static function callConsoleApi(HyvorTalkWebsite $website, string $method, string $endpoint, array $data = [])
    {
        return self::callApi('console-api', [
            'website_id' => $website->website_id,
            'method' => $method,
            'endpoint' => $endpoint,
            'data' => $data,
        ]);
    }

}
