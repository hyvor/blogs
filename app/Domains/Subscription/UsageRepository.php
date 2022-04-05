<?php
namespace App\Domains\Subscription;

use App\Data\Enums\CountEnum;
use App\Data\Objects\ConsoleAPI\BlogSubscription\UsageObject;
use App\Domains\Count\CountRepository;
use App\Models\Blog;

class UsageRepository {

    public static function getUsage(Blog $blog) : array {

        $counts = CountRepository::getCounts($blog, [
            CountEnum::BLOG_USERS,
            CountEnum::BLOG_POSTS,
            CountEnum::BLOG_MEDIA
        ]);

        $limits = self::getLimits($blog);

        return [
            'users' => new UsageObject($counts[ CountEnum::BLOG_USERS->value ], $limits['users']),
            'posts' => new UsageObject($counts[ CountEnum::BLOG_POSTS->value ], $limits['posts']),
            'media' => new UsageObject($counts[ CountEnum::BLOG_MEDIA->value ], $limits['media']),
        ];

    }

    public static function getPostsUsage(Blog $blog) : UsageObject {
        return self::getUsage($blog)['users'];
    }

    public static function getUsersUsage(Blog $blog) : UsageObject {
        return self::getUsage($blog)['posts'];
    }
    
    public static function getMediaUsage(Blog $blog) : UsageObject {
        return self::getUsage($blog)['media'];
    }

    public static function getLimits(Blog $blog) : array {

        $subscription = $blog->subscription();

        $plan = $subscription && $subscription->valid() ? 
            SubscriptionRepository::getPlanConfigById($subscription->paddle_plan)['name'] : 
            null;

        $users = 0; // 0 = unlimited
        $posts = 0;
        $media = 0; // bytes

        $gb = (10 ** 9);

        if ($plan === null) {
            $users = 1;
            $posts = 100;
            $media = 1 * $gb;
        } else if ($plan === 'pro') {
            $users = 2;
            $media = 10 * $gb;
        } else if ($plan === 'team') {
            $users = 1 * $subscription->quantity;
            $media = $users * 20 * $gb;
        } else if ($plan === 'enterprise') {
            $media = 2000 * $gb;
        }

        return [
            'users' => $users,
            'posts' => $posts,
            'media' => $media
        ];

    }

}