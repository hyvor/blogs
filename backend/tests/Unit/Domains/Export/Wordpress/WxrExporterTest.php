<?php

namespace Tests\Unit\Domains\Export\Wordpress;

use App\Domains\Export\WordPress\WxrExporter;
use App\Models\Blog;
use App\Models\Language;

function generateWxr(Blog $blog, Language $language = null) {
    $filePath = tempnam(sys_get_temp_dir(), 'test');
    $exporter  = new WxrExporter($blog, $language ?? $blog->languages->first(),  $filePath);
    $exporter->write();
    return simplexml_load_file($filePath);
}

it('adds blog data', function() {

    $blog = blogWithLanguage();
    $xml = generateWxr($blog);

    expect((string) $xml->channel->title)->toBe($blog->variants->first()->name);
    expect((string) $xml->channel->link)->toBe($blog->url());
    expect((string) $xml->channel->description)->toBe($blog->variants->first()->description);
    expect((string) $xml->channel->language)->toBe($blog->languages()->first()->code);
    expect((string) $xml->channel->children('wp', true)->wxr_version)->toBe('1.2');
    expect((string) $xml->channel->children('wp', true)->base_site_url)->toBe($blog->url());
    expect((string) $xml->channel->children('wp', true)->base_blog_url)->toBe($blog->url());

});

it('adds authors', function() {

    $blog = blogWithLanguage();

    $authors = addUsers($blog, 3, ['posts_count' => 10]);
    addUsers(blogWithLanguage(), 1, ['posts_count' => 9]);

    $authors[0]->variants[0]->update(['name' => 'Supun Wimalasena']);
    $authors[1]->variants[0]->update(['name' => 'Ishini']);

    $notAuthors = addUsers($blog, 2, ['posts_count' => 0]);

    $xml = generateWxr($blog);

    $authorsXml = $xml->channel->xpath('//wp:author');
    expect($authorsXml)->toHaveCount(3);

    $author1XmlWp = $authorsXml[0]->children('wp', true);
    expect((string) $author1XmlWp->author_id)->toBe((string) $authors[0]->id);
    expect((string) $author1XmlWp->author_login)->toBe($authors[0]->slug);
    expect((string) $author1XmlWp->author_email)->toBe($authors[0]->email);
    expect((string) $author1XmlWp->author_display_name)->toBe('Supun Wimalasena');
    expect((string) $author1XmlWp->author_first_name)->toBe('Supun');
    expect((string) $author1XmlWp->author_last_name)->toBe('Wimalasena');

    $author2XmlWp = $authorsXml[1]->children('wp', true);
    expect((string) $author2XmlWp->author_display_name)->toBe('Ishini');
    expect((string) $author2XmlWp->author_first_name)->toBe('Ishini');
    expect((string) $author2XmlWp->author_last_name)->toBe('');

});

it('adds categories', function() {

    $blog = blogWithLanguage();
    $tags = addTags($blog, 3);

    $xml = generateWxr($blog);

    $tagsXml = $xml->channel->xpath('//wp:category');
    dd($tagsXml);

});