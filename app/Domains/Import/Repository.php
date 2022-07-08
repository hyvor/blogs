<?php

namespace App\Domains\Import;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;

class Repository
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $lang = [];

    public array $user = [];

    public array $tag = [];

    public array $post = [];

    public array $userVariant = [];

    public array $tagVariant = [];

    public array $postVariant = [];

    /**
    * @var array<array<string,mixed>>
    */
    // public array $pages = [];

    public function user(User $user){
        $this->user[] = $user;
    }

    public function tag(Tag $tags){
        $this->tag[] = $tags;
    }

    public function post(Post $post){
        $this->post[] = $post;
    }

    public function userVariant(UserVariant $userVariant){
        $this->userVariant[] = $userVariant;
    }

    public function tagVariant(TagVariant $tagVariant){
        $this->tagVariant[] = $tagVariant;
    }

    public function postVariant(PostVariant $postVariant){
        $this->postVariant[] = $postVariant;
    }


    public function language(
        ?string $language,
        ?string $languageCode,
    ) {
        // dd($language);
        $this->lang[] = [
            'language' => $language,
            'languageCode' => $languageCode,
        ];

        return $this->lang;
    }

    
}
