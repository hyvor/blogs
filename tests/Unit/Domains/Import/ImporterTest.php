<?php

use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Importer;
use App\Models\Blog;
use App\Models\Import;
use Symfony\Component\DomCrawler\Crawler;
use App\Domains\Import\Repository;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('Testing the importer', function (){
    
    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));
    
    $parser = new WordpressParser($file);
    
    $repo = $parser->parse();

    $blog = Blog::findOrFail(4);
    
    $import = new Import();

    $importer = new Importer($repo,$blog,$import);

    //Authors

    $userCount = User::count() + count($repo->user);
    $userVariantCount = UserVariant::count() + count($repo->userVariant);

    $importer->importAuthors();

    expect(User::count())->toBe($userCount);
    expect(UserVariant::count())->toBe($userVariantCount);    
    
    
    //Tags

    $tagCount = Tag::count() + count($repo->tag);
    $tagVariantCount = TagVariant::count() + count($repo->tagVariant);

    $importer->importTags();

    expect(Tag::count())->toBe($tagCount);
    expect(TagVariant::count())->toBe($tagVariantCount);   

    
    //Posts

    $postCount = Post::count() + count($repo->post);
    $postVariantCount = PostVariant::count() + count($repo->postVariant);

    $importer->importPosts();

    expect(Post::count())->toBe($postCount);
    expect(PostVariant::count())->toBe($postVariantCount);     

})->group('importer');