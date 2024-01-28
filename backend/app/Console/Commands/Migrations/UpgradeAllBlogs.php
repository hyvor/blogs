<?php declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Blog;
use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * This command was written to migrate all blogs to the new subscription model.
 * @since 2023-04-30
 */
class UpgradeAllBlogs extends Command
{

    public $name = 'migrate:upgrade-all-blogs';

    public function handle() : void
    {
        $blogs = Blog::all();

        foreach ($blogs as $blog) {

            $subscription = SubscriptionService::getActiveBlogSubscription($blog);

            if ($subscription)
                continue;

            SubscriptionService::createSubscription(
                $blog,
                SubscriptionPlanEnum::STARTER,
                SubscriptionFrequencyEnum::MONTHLY,
                SubscriptionStatusEnum::ACTIVE
            );

        }
    }

}