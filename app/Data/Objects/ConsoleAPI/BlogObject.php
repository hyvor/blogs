<?php
namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\CommentsTypeEnum;
use App\Models\Blog;

class BlogObject {

    public CommentsTypeEnum $comments_type;
    public ?int $comments_ht_website_id;
    public ?string $comments_ht_api_key;
    public ?string $comments_code;
    public ?string $newsletter_code;

    public function __construct(Blog $blog) {

        $this->comments_type = CommentsTypeEnum::from($blog->comments_type);
        $this->comments_ht_website_id = $blog->comments_ht_website_id;
        $this->comments_api_key = $blog->comments_api_key;
        $this->comments_code = $blog->comments_code;
        $this->newsletter_code = $blog->newsletter_code;

    }

}