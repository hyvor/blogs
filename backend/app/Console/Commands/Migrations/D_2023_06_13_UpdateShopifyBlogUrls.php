<?php declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domains\Blog\BlogService;
use App\Domains\Integrations\Shopify\ShopifyService;
use App\Models\ShopifyShop;
use Illuminate\Console\Command;

class D_2023_06_13_UpdateShopifyBlogUrls extends Command
{

    public $signature = 'migrate:update-shopify-blog-urls {--pretend}';

    public function handle() : void
    {

        $pretend = (boolean) $this->option('pretend');
        $shops = ShopifyShop::whereNotNull('blog_id')->get();
        $service = new ShopifyService;

        foreach ($shops as $shop) {

            $blog = $shop->blog;

            if (!$blog)
                continue;

            ['url' => $url] = $service->getShopData($shop);

            $blogUrl = $url . '/a/blog';
            $this->info(($pretend ? '[PRETEND] ' : '') . 'Updating shop '.$shop->domain.'\'s URL to '. $blogUrl);

            if (!$pretend) {

                BlogService::updateBlog($blog, [
                    'hosting_at' => 'self',
                    'hosting_url' => $blogUrl
                ]);

            }

        }

    }

}