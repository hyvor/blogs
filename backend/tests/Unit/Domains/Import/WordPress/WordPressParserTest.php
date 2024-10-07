<?php

namespace Tests\Unit\Domains\Import\WordPress;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\WordPress\WordPressParser;
use App\Domains\Post\Content\Nodes\Audio\Audio;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\PostContentService;
use Illuminate\Support\Facades\Http;

it('parses wordpress file', function() {

    Http::fake();

    $path = __DIR__ . '/example';
    $blog = blogWithLanguageAndRoutes();

    $messages = new JobMessageLog();
    $parser = new WordPressParser(
        $blog,
        $path,
        $messages,
    );
    $parser->parse();

    $posts = $parser->posts;
    expect($posts)->toHaveCount(4);

    // thumbnail
    $helloWorldPost = null;
    $startupPost = null;
    foreach ($posts as $post) {
        if ($post->variants[0]->slug === 'hello-world') {
            $helloWorldPost = $post;
        }
        else if ($post->variants[0]->slug === 'how-to-start-a-startup-blog-ultimate-guide-for-startup-blogs') {
            $startupPost = $post;
        }
    }

    expect($helloWorldPost)->not->toBeNull();
    expect($helloWorldPost->featuredImageUrl)->toStartWith('file://' . __DIR__ . '/example/uploads/');

    $content = $startupPost->variants[0]->content;
    $contentDoc = PostContentService::getDocumentFromJson($content, $blog);

    $image = $contentDoc->getNodes(Image::class);
    expect($image)->toHaveCount(2);
    expect($image[0]->attrs->src)->toBe('file://' . __DIR__ . '/example/uploads/2024/04/Commentsreactions-ezgif.com-video-to-gif-converter.gif');
    expect($image[1]->attrs->src)->toBe('https://hyvor.com/blog/media/image.png');

    $audio = $contentDoc->getNodes(Audio::class);
    expect($audio)->toHaveCount(1);
    expect($audio[0]->attrs->src)->toBe('file://' . __DIR__ . '/example/uploads/2024/04/dream-big.mp3');

});