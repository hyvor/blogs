<?php
namespace App\Data\Objects\DataAPI;

use App\Domains\Post\PostLanguageRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;

class PostLanguageObject {

    public int $id;
    public string $code;
    public string $name;
    public bool $is_primary;

    public function __construct(Language $language) {

        $this->id = $language->id;
        $this->code = $language->code;
        $this->name = $language->name;
        $this->is_primary =  $language->is_primary;

    }

}