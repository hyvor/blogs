<?php
namespace App\Http\Middleware\App\ConsoleAPI;

use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\User;
use Closure;
use Hyvor\HyvorConnecter\Login;
use Illuminate\Http\Request;

class ConsoleApiAccessMiddleware {

    public function __construct(Blog $blog) {
        $this->blog = $blog;
    }

    public function handle(Request $request, Closure $next) {

        if ($request->has('api_key')) {

            $apiKey = $request->input('api_key');

            if ($this->blog->api_key_console === null) {
                throw new TrustedException('Console API is not enabled');
            }

            if ($apiKey !== $this->blog->api_key_console) {
                throw new TrustedException('Invalid API key');
            }

            $owner = UserRepository::getUserByBlogIdAndHyvorUserId($this->blog->id, $this->blog->user_id);

            app()->instance(User::class, $owner);

        } else {

            // $hyvorUser = Login::check();

            $hyvorUser = 1;
            if (!$hyvorUser) {
                throw new TrustedException('You are not logged in');
            } 

            // $user = UserRepository::getUserByBlogIdAndHyvorUserId($this->blog->id, $hyvorUser->id);
            $user = UserRepository::getUserByBlogIdAndHyvorUserId($this->blog->id, $hyvorUser);

            if (!$user) {
                throw new TrustedException('You do not have access to this blog');
            }

            app()->instance(User::class, $user);

        }

        return $next($request);

    }

}