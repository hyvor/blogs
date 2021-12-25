<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller {

    private $userRepo;

    public function __construct(    
        UserRepositoryInterface $userRepo
    ) 
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(Request $request) {

        $customConsole = null;
        if (!$request->is('console*')) {
            /**
             * This is a request from a subdomain
             * that is set up for custom console (Enterprise Plan)
             */
            $customConsole = []; // data
        }

        $blogs = $this->userRepo->getBlogs(1, 'hyvor');

        $config = [
            'customConsole' => $customConsole,

            'blogs' => $blogs
        ];

        return view('console', ['config' => $config]);

    }

}