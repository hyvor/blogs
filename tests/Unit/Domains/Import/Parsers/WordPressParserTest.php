<?php

namespace Tests\Unit\Import\Parsers;

use App\Domains\Import\Parsers\WordpressParser;

it('Returns the users models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->userModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->role)->toBeObject();
        expect($value->status)->toBeObject();
        expect($value->slug)->toBeString();

        expect($repo->userModels[$key])->toBeObject();
    }


})->group('modelsTest');

it('Returns the tags models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->tagModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->slug)->toBeString();
        expect($value->posts_count)->toBeInt();
        
        expect($repo->tagModels[$key])->toBeObject();
    }


})->group('modelsTest');


it('Returns the post models', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->postModels as $key=>$value){
        
       // expect($value->id)->toBeInt();
        expect($value->blog_id)->toBeInt();
        expect($value->slug)->toBeString();
        expect($value->is_page)->toBeBool();
        expect($value->is_featured)->toBeInt();
        
        expect($repo->postModels[$key])->toBeObject();
    }


})->group('modelsTest');


it('parsers the language', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    
    $repo = $parser->parse();

    foreach ($repo->lang as $lang) {
        $language = $lang['language'];
        $languageCode = $lang['languageCode'];
    }

    $this->assertEquals('English', $language);

})->group('parserTest');

it('parsers the authors', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach($repo->authors as $key=>$value){
        
        expect($value['id'])->toBeInt();
        expect($value['name'])->toBeString();
        expect($value['email'])->toBeString();
        expect($value['status'])->toBeString();
        expect($value['role'])->toBeString();
        expect($value['slug'])->toBeString();
        
    }

    expect($repo->authors)->toBeArray();

})->group('parserTest');


it('parsers the tags', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->tags as $tag) {
        expect($tag['slug'])->toBeString();
        expect($tag['postsCount'])->toBeInt();
    }

    expect($repo->tags)->toBeArray();

})->group('parserTest');

it('parsers the posts', function (){

    $file = file_get_contents(test_unit_data_path('Import/wordpress.xml'));

    $parser = new WordpressParser($file);
    $repo = $parser->parse();

    foreach ($repo->posts as $post) {
        
        expect($post['slug'])->toBeString();
        expect($post['isPage'])->toBeBool();
        expect($post['title'])->toBeString();
        expect($post['description'])->toBeString();
        expect($post['status'])->toBeString();
        expect($post['content'])->toBeString();
    }

    expect($repo->posts)->toBeArray();

})->group('parserTest');



