<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Http\Controllers\Controller;
use App\Domains\User\UserRepository;
// use Hyvor\HyvorConnecter\User;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller
{
    // public function __invoke(Request $request, User $user)
    public function __invoke(Request $request)
    {
        $blogs = UserRepository::getBlogsOfUser(1)->map(function($userBlog) {
            return new UserBlogObject($userBlog);
        });

        $config = [
            // 'hyvorUser' => $user,
            'blogs' => $blogs,
            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => config('blogs.domain_delivery'),
                'hyvor' => config('blogs.domain_hyvor'),
            ]
        ];

        return view('console', ['config' => $config]);
    }
}
