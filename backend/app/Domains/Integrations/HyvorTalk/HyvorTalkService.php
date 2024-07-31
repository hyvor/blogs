<?php declare(strict_types=1);

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\HyvorTalkWebsite;
use Hyvor\Internal\InternalApi\ComponentType;
use Illuminate\Support\Facades\Http;

class HyvorTalkService
{

    /**
     * @param array<string, mixed> $data
     * @return array<mixed>
     */
    private static function callApi(string $endpoint, array $data)
    {

        $endpoint = ltrim($endpoint, '/');
        $hyvorTalkUrl = ComponentType::fromConfig()->getUrlOf(ComponentType::TALK);
        $url = $hyvorTalkUrl . '/api/integrations/hyvor-blogs/' . $endpoint;

        $response = Http::withHeaders([
            'X-Api-Key' => strval(config('services.hyvor_talk.api_key'))
        ])->post($url, $data);
        $json = $response->json();

        if (!$response->successful()) {
            $error = is_array($json) ? $json['error'] ?? null : null;
            throw new HyvorTalkApiException('Hyvor Talk API error: ' . $error);
        }

        return (array) $json;
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

}