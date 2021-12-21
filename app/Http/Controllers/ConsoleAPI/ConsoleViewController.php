<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller {

    private $userRepo;

    public function __construct(    
        UserRepositoryInterface $userRepo,
        SubscriptionRepositoryInterface $subscriptionRepo
    ) 
    {
        $this->userRepo = $userRepo;
        $this->subscriptionRepo = $subscriptionRepo;
    }

    public function __invoke(Request $request) {

        $customConsole = null;
        if (!$request->is('console/*')) {
            /**
             * This is a request from a subdomain
             * that is set up for custom console (Enterprise Plan)
             */
            $customConsole = []; // data
        }

        $blogs = [];
        $userBlogsFromRepo = $this->userRepo->getBlogs(1, 'hyvor');
        foreach ($userBlogsFromRepo as $userBlog) {
            $plan = null;
            foreach ($userBlog->blog->subscriptions as $sub) {
                if ($sub->valid()) {
                    $plan = $this->subscriptionRepo->getPlanNameByPlanId($sub->paddle_plan);
                }
            }

            $blogs[] = [
                'id' => $userBlog->blog_id,
                'role' => $userBlog->role,
                'name' => $userBlog->blog->name,
                'subdomain' => $userBlog->blog->subdomain,
                'plan' => $plan
            ];
        }

        $config = [
            'customConsole' => $customConsole,

            'blogs' => $blogs
        ];

        return view('console', ['config' => $config]);

    }

}