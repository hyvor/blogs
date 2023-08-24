<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Models\ApiKey;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Media;
use App\Models\Navigation;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Redirect;
use App\Models\Route;
use App\Models\Tag;
use App\Models\ThemeFile;
use App\Models\User;
use App\Models\Webhook;
use Closure;
use Illuminate\Http\Request;

class ResourceAccessMiddleware
{

    private Blog $blog;

    /**
     * @var array<string, class-string>
     */
    private $models = [
        '/post' => Post::class,
        '/media' => Media::class,
        '/redirect' => Redirect::class,
        '/navigation' => Navigation::class,
        '/language' => Language::class,
        '/tag' => Tag::class,
        '/user' => User::class,
        '/route' => Route::class,
        '/api-key' => ApiKey::class,
        '/webhook' => Webhook::class,
        '/theme/file' => ThemeFile::class,
    ];

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function handle(Request $request, Closure $next) : mixed
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
             * An ID is present in the path
             * Which means that we have to verify that the
             */

            // ex: api/console/v0/blog/test/post/1
            $path = $request->path();
            preg_match('~api/console/v0/blog/[^/]+(/[a-z/-]+)/\d+~', $path, $matches);

            // ex: /post
            $routePrefix = $matches[1];

            if (! array_key_exists($routePrefix, $this->models)) {
                throw new TrustedException("Unable to find the $routePrefix to verify blog relationship");
            }

            // ex: Post model
            $modelClass = $this->models[$routePrefix];
            $model = $modelClass::find($id);

            if (! $model) {
                throw new TrustedException(
                    "Unable to find the $routePrefix",
                    TrustedException::ERROR_NOT_FOUND
                );
            }

            app()->instance($modelClass, $model);

            // now check if the model's blog_id
            // is currently accessed blog's ID
            if ($model->blog_id !== $this->blog->id) {
                throw new TrustedException(
                    "This $routePrefix belongs to another blog. Ensure the subdomain is correct",
                    TrustedException::ERROR_FORBIDDEN
                );
            }
        }

        /**
         * Sets language for variant routes
         */
        // ex: api/console/v0/blog/{subdomain}/navigation/{id}/variant
        $uri = strval($request->route()?->uri());

        if (str_ends_with($uri, '/variant')) {
            $languageId = intval($request->input('language_id'));

            if (!$languageId) {
                throw new TrustedException('Language ID not set');
            }

            $language = LanguageRepository::getLanguageById($this->blog, $languageId);

            if (!$language) {
                throw new TrustedException('Language not found');
            }

            app()->instance(Language::class, $language);
        }

        if ($request->has('post_variant_id')) {
            $postVariantId = $request->integer('post_variant_id');
            if (!$postVariantId) {
                throw new TrustedException('Post variant ID not set');
            }

            $postVariant = PostRepository::getPostVariantById($postVariantId);

            if (!$postVariant) {
                throw new TrustedException('Post variant not found');
            }

            $blogId = $postVariant->post?->blog_id;

            if (!$blogId || $blogId !== $this->blog->id) {
                throw new TrustedException('Post variant does not belong to this blog');
            }

            app()->instance(PostVariant::class, $postVariant);
        }

        return $next($request);
    }
}
