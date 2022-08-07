<?php

namespace App\Domains\Integrations\Shopify;

use App\Exceptions\TrustedException;
use App\Models\ShopifyShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Str;

class ShopifyService
{
    private const NONCE_SESSION_KEY = 'shopify_nonce';

    public function generateNonce() : string
    {
        $nonce = Str::random();
        session([self::NONCE_SESSION_KEY => $nonce]);

        return $nonce;
    }

    public function hasValidNonce(string $receivedNonce) : bool
    {
        $nonce = session(self::NONCE_SESSION_KEY);
        return $nonce && $nonce === $receivedNonce;
    }

    /**
     * @param array{string: mixed} $params
     */
    public function hasValidHmac(array $params) : bool
    {
        $hmac = $params['hmac'];

        $data = collect($params)
            ->filter(fn ($val, $key) => $key !== 'hmac')
            ->map(fn ($val, $key) => http_build_query([$key => $val]))
            ->implode('&');

        $hash = hash_hmac('sha256', $data, config('integrations.shopify.api_secret_key'));

        return hash_equals($hash, $hmac);
    }

    public function getOAuthUrl(string $shopDomain)
    {
        $apiKey = config('integrations.shopify.api_key');
        $redirectUri = urlencode(URL::route('shopify-installed'));
        $nonce = self::generateNonce();

        return "https://$shopDomain/admin/oauth/authorize" .
            "?client_id=$apiKey" .
            "&scope=read_content" .
            "&redirect_uri=$redirectUri" .
            "&state=$nonce" .
            "&grant_options[]=value";
    }

    public function getAccessToken(string $domain, string $code) : string
    {

        $response = Http::post("https://$domain/admin/oauth/access_token", [
            'client_id' => config('integrations.shopify.api_key'),
            'client_secret' => config('integrations.shopify.api_secret_key'),
            'code' => $code
        ]);

        if (!$response->successful()) {
            throw new TrustedException('Could not get access token (HTTP Failure)');
        }

        return $response->json()['access_token'];

    }

    public function createShop(string $domain, string $accessToken)
    {
        ShopifyShop::create([
            'domain' => $domain,
            'access_token' => $accessToken
        ]);
    }

    public function getShopByDomain(string $domain) : ?ShopifyShop
    {
        return ShopifyShop::where('domain', $domain)->first();
    }

}