<?php

namespace App\Http\Controllers\Integrations\Shopify;

use Illuminate\Http\Request;

class ShopifyController
{

    public function init(Request $request)
    {

        $shop = $request->input('shop');
        $apiKey = config('integrations.shopify.api_key');
        $redirectUri = urlencode('https://3902-77-140-61-8.ngrok.io/integrations/shopify/installed');
        $nonce = 'o1lk2ke1';

        return redirect("https://$shop/admin/oauth/authorize?client_id=$apiKey&scope=read_content&redirect_uri=$redirectUri&state=$nonce&grant_options[]=value");

    }

    public function installed(Request $request)
    {
        dd("INSTALLED");
    }

    public function proxy(Request $request)
    {
        dump($request->all());
        return response('');
    }

}