<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller
{
    public function __invoke(Request $request)
    {
        $hyvorUser = Login::check();

        if ($hyvorUser === null) {
            return Redirect::toLogin();
        }

        $blogs = UserRepository::getBlogsOfUser($hyvorUser->id)->mapInto(UserBlogObject::class);

        $config = [
            // state

            'hyvorUser' => $hyvorUser,
            'blogs' => $blogs,

            // static

            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => config('blogs.domain_delivery'),
                'hyvor' => config('blogs.domain_hyvor'),
            ],

            'syntax_themes' => Highlighter::getAllThemes(),

            'limits' => [
                'max_theme_zip_size_kb' => config('limits.max_theme_zip_size_kb')
            ]
        ];

        return view('console', ['config' => $config]);
    }
}
