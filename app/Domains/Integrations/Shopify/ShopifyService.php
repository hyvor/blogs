<?php

namespace App\Domains\Integrations\Shopify;

use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\ShopifyShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Str;

class ShopifyService
{
    private const NONCE_SESSION_KEY = 'shopify_nonce';

    public function generateNonce(): string
    {
        $nonce = Str::random();
        session([self::NONCE_SESSION_KEY => $nonce]);

        return $nonce;
    }

    public function hasValidNonce(string $receivedNonce): bool
    {
        $nonce = session(self::NONCE_SESSION_KEY);
        return $nonce && $nonce === $receivedNonce;
    }

    /**
     * @param array{string: mixed} $params
     */
    public function hasValidHmac(array $params): bool
    {
        $hmac = $params['hmac'];

        $data = collect($params)
            ->filter(fn ($val, $key) => $key !== 'hmac')
            ->map(fn ($val, $key) => http_build_query([$key => $val]))
            ->implode('&');

        $hash = hash_hmac('sha256', $data, config('services.shopify.api_secret_key'));

        return hash_equals($hash, $hmac);
    }

    /**
     * @source https://shopify.dev/apps/online-store/app-proxies#calculate-a-digital-signature
     */
    public function hasValidProxySignature(array $params): bool
    {
        $signature = $params['signature'];

        $data = collect($params)
            ->sortKeys()
            ->filter(fn ($val, $key) => $key !== 'signature')
            ->map(function ($val, $key) {
                $val ??= '';
                if (is_array($val)) {
                    $val = implode(',', $val);
                }
                return "$key=$val";
            })
            ->implode('');

        $hash = hash_hmac('sha256', $data, config('services.shopify.api_secret_key'));

        return hash_equals($hash, $signature);
    }

    public function hasValidWebhookSignature(string $signature, string $body)
    {

        $hash = base64_encode(hash_hmac('sha256', $body, config('services.shopify.api_secret_key'), true));
        return hash_equals($signature, $hash);

    }

    public function getOAuthUrl(string $shopDomain)
    {
        $apiKey = config('services.shopify.api_key');
        $redirectUri = urlencode(URL::route('shopify-installed'));
        $nonce = self::generateNonce();

        return "https://$shopDomain/admin/oauth/authorize" .
            "?client_id=$apiKey" .
            "&scope=read_content" .
            "&redirect_uri=$redirectUri" .
            "&state=$nonce" .
            "&grant_options[]=value";
    }

    public function getAccessToken(string $domain, string $code): string
    {
        $response = Http::post("https://$domain/admin/oauth/access_token", [
            'client_id' => config('services.shopify.api_key'),
            'client_secret' => config('services.shopify.api_secret_key'),
            'code' => $code
        ]);

        if (!$response->successful()) {
            throw new TrustedException('Could not get access token (HTTP Failure)');
        }

        return $response->json()['access_token'];
    }

    public function createShop(string $domain, string $accessToken)
    {
        ShopifyShop::updateOrCreate(
            ['domain' => $domain],
            ['access_token' => $accessToken]
        );
    }

    public static function deleteShop(ShopifyShop $shop)
    {
        $shop->delete();
    }

    public function getShopByDomain(string $domain): ?ShopifyShop
    {
        return ShopifyShop::where('domain', $domain)->first();
    }

    public static function getShopByBlog(Blog $blog): ?ShopifyShop
    {
        return ShopifyShop::where('blog_id', $blog->id)->first();
    }
}
