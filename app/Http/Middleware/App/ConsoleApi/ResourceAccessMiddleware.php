<?php

namespace App\Http\Middleware\App\ConsoleApi;

use App\Domains\Language\LanguageRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Media;
use App\Models\Navigation;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Route;
use App\Models\Tag;
use App\Models\User;
use Closure;

class ResourceAccessMiddleware
{
    private $models = [
        'post' => Post::class,
        'media' => Media::class,
        'redirect' => Redirect::class,
        'navigation' => Navigation::class,
        'language' => Language::class,
        'tag' => Tag::class,
        'user' => User::class,
        'route' => Route::class,
    ];

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function handle($request, Closure $next)
    {

        /**
         * If there's an ID in the route,
         * it means that we are accesing a model that belongs to the current blog
         * such as a post that belong to the post
         *
         * We verify that in this middleware.
         *
         * (If we do that in a controller, we will need to do it on each handler)
         */
        $id = $request->route('id');
        if ($id) {
            /**
             *
             * An ID is present in the path
             * Which means that we have to verify that the
             *
             */

            // ex: api/console/v0/blog/supun/post/1
            $path = $request->path();

            // ex: [api, console, v0, blog, supun, post, 1]
            $split = explode("/", $path);

            // ex: post (model type)
            $modelType = $split[5];

            if (! array_key_exists($modelType, $this->models)) {
                throw new TrustedException("Unable to find the $modelType to verify blog relationship");
            }

            // ex: Post model
            $model = $this->models[$modelType]::find($id);

            if (! $model) {
                throw new TrustedException(
                    "Unable to find the $modelType",
                    TrustedException::ERROR_NOT_FOUND
                );
            }

            app()->instance($this->models[$modelType], $model);


            // now check if the model's blog_id
            // is currently accessed blog's ID
            if ($model->blog_id !== $this->blog->id) {
                throw new TrustedException(
                    "This $modelType belongs to another blog. Ensure the subdomain is correct",
                    TrustedException::ERROR_FORBIDDEN
                );
            }
        }

        /**
         * Sets language for variant routes
         */
        // ex: api/console/v0/blog/{subdomain}/navigation/{id}/variant
        $uri = $request->route()->uri();
        if (str_ends_with($uri, '/variant')) {
            $languageId = $request->input('language_id');

            if (empty($languageId)) {
                throw new TrustedException('Language ID not set');
            }

            $language = LanguageRepository::getLanguageById($this->blog, $languageId);

            if (! $language) {
                throw new TrustedException('Language not found');
            }

            app()->instance(Language::class, $language);
        }

        return $next($request);
    }
}
