<?php

namespace App\Http\Controllers\Integrations\Shopify;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Integrations\Shopify\Rules\ShopDomainRule;
use App\Domains\Integrations\Shopify\ShopifyService;
use App\Exceptions\TrustedException;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ShopifyController
{

    /**
     * Called when the user clicks the Add App button in the Shopify App
     */
    public function init(Request $request, ShopifyService $shopify)
    {

        $request->validate([
            'shop' => ['required', 'string', new ShopDomainRule],
        ]);

        $shopDomain = $request->input('shop');

        if ($shopify->getShopByDomain($shopDomain)) {
            throw new TrustedException('Shop already exists');
        }

        $url = $shopify->getOAuthUrl($shopDomain);

        return redirect($url);
    }

    /**
     * Called after the user confirms by clicking the Install App button
     * Here, we save the access token but does not create the blog
     * If all is good, the user is redirected to the /complete endpoint to check Hyvor login
     * and create the blog
     */
    public function installed(Request $request, ShopifyService $shopify)
    {
        $request->validate([
            'code' => 'required|string',
            'hmac' => 'required|string',
            'shop' => ['required', 'string', new ShopDomainRule],
            'state' => 'required|string',
        ]);

        if (!$shopify->hasValidHmac($request->all()))
            throw new TrustedException('HMAC hash is invalid');

        $nonce = $request->input('state');
        if (!$shopify->hasValidNonce($nonce))
            throw new TrustedException('Nonce is invalid');

        $domain = $request->input('shop');
        $code = $request->input('code');
        $accessToken = $shopify->getAccessToken($domain, $code);

        $shopify->createShop($domain, $accessToken);

        return redirect(URL::route('shopify-complete', [
            'domain' => $domain
        ]));
    }

    /**
     * Here we check the Hyvor login and then create the blog
     * And updates the blog to self-hosting (for Shopify Proxy)
     */
    public function complete(Request $request, ShopifyService $shopifyService)
    {

        $request->validate([
            'domain' => 'required|string'
        ]);

        $domain = $request->input('domain');

        $shop = $shopifyService->getShopByDomain($domain);

        if (!$shop) {
            throw new TrustedException('Shop not found: ' . $domain);
        }

        if ($shop->blog_id) {
            throw new TrustedException('Shop already assigned to a blog');
        }

        $hyvorUser = Login::check();

        if (!$hyvorUser) {
            return Redirect::toSignup("/integrations/shopify/complete?domain=$domain");
        }

        $blog = app(BlogService::class)->createBlog(
            $hyvorUser->id,
            $domain,
            str_replace('.', '-', $domain),
            BlogTypeEnum::DEFAULT,
            BlogBillingTypeEnum::SHOPIFY
        );

        // setup blog for self-hosting with shopify default configs
        BlogService::updateBlog($blog, [
            'hosting_at' => BlogHostingAtEnum::SELF,
            'hosting_url' => "https://$domain/a/blog"
        ]);

        $shop->blog_id = $blog->id;
        $shop->save();

        return redirect("/console/$blog->subdomain");

    }

    public function proxy(Request $request)
    {
        dump($request->all());
        return response('');
    }

}