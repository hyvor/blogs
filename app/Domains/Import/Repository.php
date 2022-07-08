<?php

namespace App\Domains\Import;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;


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

    public function user(?object $user){
        $this->user[] = $user;
    }

    public function tag(?object $tags){
        $this->tag[] = $tags;
    }

    public function post(?object $post){
        $this->post[] = $post;
    }

    public function userVariant(?object $userVariant){
        $this->userVariant[] = $userVariant;
    }

    public function tagVariant(?object $tagVariant){
        $this->tagVariant[] = $tagVariant;
    }

    public function postVariant(?object $postVariant){
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
