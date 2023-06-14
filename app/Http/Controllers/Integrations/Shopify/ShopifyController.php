<?php declare(strict_types=1);

namespace App\Http\Controllers\Integrations\Shopify;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogIntegrationEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Blog\Jobs\DeleteBlogJob;
use App\Domains\Blog\UniqueSubdomainGenerator;
use App\Domains\Delivery\DeliveryService;
use App\Domains\Integrations\Shopify\Rules\ShopDomainRule;
use App\Domains\Integrations\Shopify\ShopifyService;
use App\Domains\Subscription\SubscriptionService;
use App\Exceptions\TrustedException;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Enum;

class ShopifyController
{
    /**
     * Called when the user clicks the Add App button in the Shopify App
     */
    public function init(Request $request, ShopifyService $shopify) : mixed
    {
        $request->validate([
            'shop' => ['required', 'string', new ShopDomainRule()],
        ]);

        $shopDomain = (string) $request->string('shop');

        $shop = $shopify->getShopByDomain($shopDomain);
        if ($shop && $shop->blog) {
            return redirect('/console/' . $shop->blog->subdomain);
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
            'shop' => ['required', 'string', new ShopDomainRule()],
            'state' => 'required|string',
        ]);

        if (!$shopify->hasValidHmac($request->all())) {
            throw new TrustedException('HMAC hash is invalid');
        }

        $nonce = $request->input('state');
        if (!$shopify->hasValidNonce($nonce)) {
            throw new TrustedException('Nonce is invalid');
        }

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
    public function complete(Request $request, ShopifyService $shopifyService) : mixed
    {
        $request->validate([
            'domain' => 'required|string'
        ]);

        $domain = (string) $request->string('domain');

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

        ['name' => $shopName, 'url' => $shopUrl] = $shopifyService->getShopData($shop);

        $blog = app(BlogService::class)->createBlog(
            $hyvorUser->id,
            $shopName,
            UniqueSubdomainGenerator::generate([$shopName]),
            BlogTypeEnum::DEFAULT,
            BlogBillingTypeEnum::SHOPIFY,
            BlogIntegrationEnum::SHOPIFY
        );

        // setup blog for self-hosting with shopify default configs
        BlogService::updateBlog($blog, [
            'hosting_at' => 'self',
            'hosting_url' => $shopUrl . '/a/blog'
        ]);

        $shop->blog_id = $blog->id;
        $shop->save();

        return redirect("/console/$blog->subdomain");
    }

    public function proxy(Request $request, ShopifyService $shopifyService) : mixed
    {

        $request->validate([
            'shop' => ['required', 'string', new ShopDomainRule()],
            'signature' => 'required|string'
        ]);

        if (!$shopifyService->hasValidProxySignature($request->all())) {
            throw new TrustedException('Invalid signature');
        }

        $domain = (string) $request->string('shop');

        $shop = $shopifyService->getShopByDomain($domain);

        if (!$shop) {
            throw new TrustedException('Shop not found');
        }

        $blog = $shop->blog;

        if (!$blog) {
            throw new TrustedException('No blog is assigned to this shop');
        }

        $path = strval($request->route('path') ?? '');
        return DeliveryService::getLaravelResponse($blog, $path);

    }

    public function confirmSubscription(Request $request)
    {
        if (!$request->hasValidSignatureWhileIgnoring(['charge_id'])) {
            throw new TrustedException('Invalid signature');
        }

        $request->validate([
            'charge_id' => 'required|integer',
            'blog_id' => 'required|int',
            'plan' => ['required', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', new Enum(SubscriptionFrequencyEnum::class)],
        ]);

        $blogId = (int) $request->input('blog_id');
        $blog = BlogService::getBlogById($blogId);

        if (!$blog) {
            throw new TrustedException('Blog not found');
        }

        $plan = SubscriptionPlanEnum::from($request->input('plan'));
        $frequency = SubscriptionFrequencyEnum::from($request->input('frequency'));

        // cancel current subscription
        $currentSubscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($currentSubscription) {
            SubscriptionService::cancelSubscription($currentSubscription, now());
        }

        $subscription = SubscriptionService::createSubscription(
            $blog,
            $plan,
            $frequency
        );

        $chargeId = (int) $request->input('charge_id');
        $subscription->setMeta('shopify_charge_id', $chargeId);

        return redirect("/console/$blog->subdomain/billing");
    }

    public function deleteShop(Request $request, ShopifyService $shopifyService)
    {

        $request->validate([
            'shop_domain' => ['required', 'string', new ShopDomainRule()]
        ]);

        if (!$shopifyService->hasValidWebhookSignature(
            $request->header('X-Shopify-Hmac-SHA256'),
            $request->getContent()
        )) {
            abort(401);
        }

        $domain = $request->input('shop_domain');

        $shop = $shopifyService->getShopByDomain($domain);

        if (!$shop) {
            throw new TrustedException('Shop not found');
        }

        if (!$shop->blog) {
            throw new TrustedException('No blog assigned');
        }

        // deletes the shop via events
        DeleteBlogJob::dispatch($shop->blog);

        return response()->json();
    }
}
