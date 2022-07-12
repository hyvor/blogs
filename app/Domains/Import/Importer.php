<?php

namespace App\Domains\Import;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Import;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;

use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserVariantCreatedEvent;

class Importer
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $authorIds = [];

    public array $tagIds = [];

    public array $postIds = [];

    public function __construct(Repository $repository, Blog $blog, Import $import)
    {
        $this->repository = $repository;
        $this->blog = $blog;
        $this->import = $import;
    }

    public function import()
    {
        $this->importAuthors();
        $this->importTags();
        $this->importPosts();    
    }

    public function importAuthors(){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->user as $key=>$user) {
            
            $authorId  = $user->id;  
            unset($user->id);
            $user->blog_id = $this->blog->id;
          
            $user = User::create($user->getAttributes());   
            
            $userVariant = $this->repository->userVariant[$key];
            
            unset($userVariant->id);
            $userVariant->user_id = $user->id;
            $userVariant->language_id = $getLanguage->id;
          
            $userVariant  = UserVariant::create($userVariant->getAttributes());
            
            $this->authorIds[] = ['user_id'=>$user->id,'userVariant_id'=>$userVariant->id]; 
        }    

        return $this->authorIds;

    }

    public function importTags(){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->tag as $key=>$tag) {
            
            $authorId  = $tag->id;  
            unset($tag->id);
            $tag->blog_id = $this->blog->id;
            $tag->posts_count = count($this->repository->post);
          
            $tag = Tag::create($tag->getAttributes());   
            
            $tagVariant = $this->repository->tagVariant[$key];
            
            unset($tagVariant->id);
            $tagVariant->tag_id = $tag->id;
            $tagVariant->language_id = $getLanguage->id;
          
            $tagVariant  = TagVariant::create($tagVariant->getAttributes());

            $this->tagIds[] = ['tag_id'=>$tag->id,'tagVariant_id'=>$tagVariant->id];
        } 

        return $this->tagIds;   
    }


    public function importPosts(){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->post as $key=>$post) {
            
            $authorId  = $post->id;  
            unset($post->id);
            
            $post->blog_id = $this->blog->id;
           
            $post = Post::create($post->getAttributes());   
            
            $postVariant = $this->repository->postVariant[$key];
            
            unset($postVariant->id);
            $postVariant->post_id = $post->id;
            $postVariant->language_id = $getLanguage->id;
              
            $postVariant  = PostVariant::create($postVariant->getAttributes());
               
            $this->postIds[] = ['post_id'=>$post->id,'postVariant_id'=>$postVariant->id];
        }

        return $this->postIds;    
    }
    
}
