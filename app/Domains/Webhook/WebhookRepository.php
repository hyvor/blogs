<?php
namespace App\Domains\Webhook;

use App\Models\Blog;

class WebhookRepository
{

    public static function getWebhooks(Blog $blog)
    {
        return $blog->webhooks;
    }

    public static function createWebhook(Blog $blog)
    {
        $blog->webhooks()->create();
    }

    public static function updateWebhook()
    {

        

    }

    public static function deleteWebhook()
    {

    }

}