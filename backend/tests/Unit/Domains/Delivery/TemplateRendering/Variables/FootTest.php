<?php declare(strict_types=1);

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Subscription;

it('sets _foot in index', function () {
    $blog = blogWithLanguageAndRoutes();
    $variant = $blog->variants[0];

    $codeFoot = 'This is code head {{ _blog.name }}';
    $codeFootRendered = htmlspecialchars("This is code head $variant->name");

    $blog->setMeta([
        'code_foot' => $codeFoot,
    ]);

    $content = '{{ _foot | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain('flashload.js');
    expect($content)->toContain($codeFootRendered);
});

it('sets _foot in a post page', function () {
    $blog = blogWithLanguageAndRoutes();
    $post = addPublishedPost($blog);

    $postCodeFoot = 'A post code head {{ _post.id }}';
    $postCodeFootRendered = "A post code head $post->id";
    $post->update(['code_foot' => $postCodeFoot]);

    $content = '{{ _foot | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/{$post->variants[0]->slug}");
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain($postCodeFootRendered);
});

it('sets flashload basepath', function() {

    $blog = blogWithLanguageAndRoutes();
    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: ""');

});

it('sets flashload basepath to /blog', function() {

    $blog = blogWithLanguageAndRoutes();
    $blog->hosting_at = BlogHostingAtEnum::SELF;
    $blog->hosting_url = 'https://hyvor.com/blog';
    $blog->save();

    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: "blog"');

});

it('disables flashload', function() {
    $blog = blogWithLanguageAndRoutes();
    $blog->setMeta('flashload', false);

    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->not()->toContain('flashload.js');
});

it('sets flashload basepath to /blog/page', function() {

    $blog = blogWithLanguageAndRoutes();
    $blog->hosting_at = BlogHostingAtEnum::SELF;
    $blog->hosting_url = 'https://hyvor.com/blog/page';
    $blog->save();

    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: "blog/page"');

});

it('adds tag code', function() {

    $blog = blogWithLanguageAndRoutes();

    $post = addPublishedPost($blog);
    $tag = addTag($blog, [
        'code_foot' => 'This is tag code head for {{ _post.slug }}'
    ]);
    addTagToPost($post, $tag);

    $otherTag = addTag($blog, ['code_foot' => 'Not tag']);

    $content = '{{ _foot | template }}';
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/{$post->variants[0]->slug}");
    $responseObject = $pathMatcher->getResponseObject();
    $content = $responseObject->content;

    expect($content)->toContain('This is tag code head for ' . $post->variants[0]->slug);
    expect($content)->not->toContain('Not tag');

});

/*it('adds powered by for free plan blogs', function() {

    $blog = blogWithLanguageAndRoutes();
    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('Hyvor Blogs');

});*/

/*it('does not add powered by to non-free blogs', function() {

    $blog = blogWithLanguageAndRoutes();
    addThemeTemplateFile($blog, '{{ _foot | template }}');
    Subscription::factory()->create(['blog_id' => $blog]);
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->not->toContain('Hyvor Blogs');

});

it('does not add powered by to dev and preview blogs', function() {

    $blog = blogWithLanguageAndRoutes(['type' => 'dev']);
    addThemeTemplateFile($blog, '{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->not->toContain('Hyvor Blogs');

});*/
