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
    public array $tagIdArray = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $authorIdArray = [];

    public function __construct(Repository $repository, Blog $blog, Import $import)
    {
        $this->repository = $repository;
        $this->blog = $blog;
        $this->import = $import;
    }

    public function import()
    {
        
        $authorCount = count($this->repository->user);
        $tagCount = count($this->repository->tag);
        $postCount = count($this->repository->post);

        $this->importAuthors();
        $this->importTags($postCount);
        $this->importPosts();    
    }

    public function importAuthors(){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->user as $key=>$user) {
            
            $authorId  = $user->id;  
            unset($user->id);
            $user->blog_id = $this->blog->id;
          
            $user = User::create($user->getAttributes());   
            UserCreatedEvent::dispatch($user);
          
           // dd($user->getAttributes());
            
            $userVariant = $this->repository->userVariant[$key];
            
            if($userVariant->user_id == $authorId){
             
                unset($userVariant->id);
                $userVariant->user_id = $user->id;
                $userVariant->language_id = $getLanguage->id;
              
                $userVariant  = UserVariant::create($userVariant->getAttributes());
                UserVariantCreatedEvent::dispatch($userVariant);
            }
            
            dd('cool');
        }    

    }

    public function importTags($postCount){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->tag as $key=>$tag) {
            
            $authorId  = $tag->id;  
            unset($tag->id);
            $tag->blog_id = $this->blog->id;
            $tag->posts_count = $postCount;
          
            $tag = Tag::create($tag->getAttributes());   
            
            //dd($tag->getAttributes());
            
            $tagVariant = $this->repository->tagVariant[$key];
            
            if($tagVariant->tag_id == $authorId){
             
                unset($tagVariant->id);
                $tagVariant->tag_id = $tag->id;
                $tagVariant->language_id = $getLanguage->id;
              
                $tagVariant  = TagVariant::create($tagVariant->getAttributes());
               
                dd($tagVariant->getAttributes());
            }
            
            dd('cool');
        }    
    }


    public function importPosts(){

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        
        foreach ($this->repository->post as $key=>$post) {
            
            $authorId  = $post->id;  
            unset($post->id);
            $post->blog_id = $this->blog->id;
           
            $post = Post::create($post->getAttributes());   
            
            //dd($post->getAttributes());
            
            $postVariant = $this->repository->postVariant[$key];
            
            if($postVariant->post_id == $authorId){
             
                unset($postVariant->id);
                $postVariant->post_id = $post->id;
                $postVariant->language_id = $getLanguage->id;
              
                $postVariant  = PostVariant::create($postVariant->getAttributes());
               
               // dd($postVariant->getAttributes());
            }
            
            dd('cool');
        }    
    }
    
}
