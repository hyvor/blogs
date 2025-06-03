<?php declare(strict_types=1);

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\HyvorTalkWebsite;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;

class HyvorTalkService
{
    public function __construct(private readonly InternalApi $internalApi) {}

    /**
     * @param 'create-website'|'set-domains'|'console-api' $endpoint
     * @param array<string, mixed> $data
     * @return array<mixed>
     * @throws InternalApiCallFailedException
     */
    private function callApi(string $endpoint, array $data)
    {
        return $this->internalApi->call(
            Component::TALK,
            "/blogs/integration/" . $endpoint,
            $data
        );
    }

    public function getHyvorTalkWebsite(Blog $blog): ?HyvorTalkWebsite
    {
        return HyvorTalkWebsite::where("blog_id", $blog->id)->first();
    }

    public function createHyvorTalkWebsite(Blog $blog): HyvorTalkWebsite
    {
        $domain = PermalinkRepository::getBlogDomain($blog);

        $data = $this->callApi("create-website", [
            "name" => $blog->subdomain,
            "domain" => $domain,
            "hyvor_user_id" => $blog->hyvor_user_id,
        ]);

        return HyvorTalkWebsite::create([
            "blog_id" => $blog->id,
            "website_id" => intval($data["id"]),
        ]);
    }

    public function deleteHyvorTalkWebsite(HyvorTalkWebsite $website): void
    {
        $website->delete();
    }

    /**
     * @param string[] $domains
     */
    public function updateDomains(
        HyvorTalkWebsite $website,
        array $domains
    ): void {
        $this->callApi("set-domains", [
            "website_id" => $website->website_id,
            "domains" => $domains,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return mixed[]
     * @throws InternalApiCallFailedException
     */
    public function callConsoleApi(
        HyvorTalkWebsite $website,
        string $method,
        string $endpoint,
        array $data = []
    ) {
        return $this->callApi("console-api", [
            "website_id" => $website->website_id,
            "method" => $method,
            "endpoint" => $endpoint,
            "data" => $data,
        ]);
    }
}
