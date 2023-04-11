<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Data\Objects\ConsoleAPI\Media\MediaObject;
use App\Data\Objects\ConsoleAPI\Navigation\NavigationObject;
use App\Data\Objects\ConsoleAPI\Post\PostObject;
use App\Data\Objects\ConsoleAPI\RedirectObject;
use App\Data\Objects\ConsoleAPI\RouteObject;
use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\User\UserObject;
use Hyvor\JsonExporter\File;

class HyvorBlogsExporter extends ExporterAbstract
{

    private File $file;

    public function createFile(): string
    {

        $path = $this->getTemporaryFilePath('json');

        $this->file = new File($path);

        $this->addBlog();
        $this->addLanguages();
        $this->addPosts();
        $this->addUsers();
        $this->addTags();
        $this->addMedia();
        $this->addNavigation();
        $this->addRoutes();
        $this->addRedirects();

        $this->file->end();

        return $path;

    }

    private function addBlog() : void
    {
        $this->file->value('blog', new BlogObject($this->blog));
    }

    private function addLanguages() : void
    {
        /** @var LanguageObject[] $languages */
        $languages = $this->blog->languages->mapInto(LanguageObject::class)->toArray();
        $this->file->collection('languages')->addItems($languages);
    }

    private function addPosts() : void
    {
        $postsWriter = $this->file->collection('posts');
        $this->blog->posts()->chunk(1000, function($posts) use ($postsWriter) {
            /** @var PostObject[] $objects */
            $objects = $posts->map(fn ($post) => new PostObject($post, $this->blog))->toArray();
            $postsWriter->addItems($objects);
        });
    }

    private function addUsers() : void
    {
        $usersWriter = $this->file->collection('users');
        $this->blog->users()->chunk(1000, function($users) use ($usersWriter) {
            /** @var UserObject[] $objects */
            $objects = $users->map(fn ($user) => new UserObject($user, $this->blog))->toArray();
            $usersWriter->addItems($objects);
        });
    }

    private function addTags() : void
    {
        $tagsWriter = $this->file->collection('tags');
        $this->blog->tags()->chunk(1000, function($tags) use ($tagsWriter) {
            /** @var TagObject[] $objects */
            $objects = $tags->map(fn ($tag) => new TagObject($tag, $this->blog))->toArray();
            $tagsWriter->addItems($objects);
        });
    }

    private function addMedia() : void
    {
        $mediaWriter = $this->file->collection('media');
        $this->blog->medias()->chunk(1000, function($medias) use ($mediaWriter) {
            /** @var MediaObject[] $objects */
            $objects = $medias->map(fn ($media) => new MediaObject($media, $this->blog))->toArray();
            $mediaWriter->addItems($objects);
        });
    }

    private function addNavigation() : void
    {
        /** @var NavigationObject[] $languages */
        $languages = $this->blog->navigations->mapInto(NavigationObject::class)->toArray();
        $this->file->collection('navigation')->addItems($languages);
    }

    private function addRoutes() : void
    {
        /** @var RouteObject[] $routes */
        $routes = $this->blog->routes->mapInto(RouteObject::class)->toArray();
        $this->file->collection('routes')->addItems($routes);
    }

    private function addRedirects() : void
    {
        /** @var RedirectObject[] $routes */
        $routes = $this->blog->redirects->mapInto(RedirectObject::class)->toArray();
        $this->file->collection('redirects')->addItems($routes);
    }

}