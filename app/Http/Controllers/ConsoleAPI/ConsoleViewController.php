<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use App\Domains\User\UserRepository;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller
{
    public function __invoke(Request $request)
    {
        $hyvor = [];
        $blogs = UserRepository::getBlogsOfUser(1);

        $config = [
            'hyvorAccount' => $hyvor,
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
