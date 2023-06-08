<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\Import;

class Importer
{

    public function __construct(
        private readonly Blog $blog,
        private readonly Import $import,
        private readonly ParserAbstract $parser,
        private readonly bool $importImages,
    ) {}

    public function import() : void
    {
        $this->parser->parse();
        $this->importPosts();
    }

    private function importPosts() : void
    {

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($this->blog);
        $owner = UserRepository::getOwnerOfBlog($this->blog);

        foreach ($this->parser->posts as $importingPost)
        {

            $post = PostRepository::createPost(
                $this->blog,
                [
                    'published_at' => $importingPost->publishedAt,
                    'featured_image_url' => $importingPost->featuredImageUrl,
                    'is_page' => $importingPost->isPage,
                    'is_featured' => $importingPost->isFeatured
                ]
            );

            foreach ($importingPost->variants as $importingVariant) {

                PostRepository::updatePostVariant(
                    $post,
                    $primaryLanguage,
                    [
                        'slug' => $importingVariant->slug,
                        'title' => $importingVariant->title,
                        'description' => $importingVariant->description,
                        'content' => $importingVariant->content,
                        'status' => $importingVariant->status
                    ]
                );

            }

            PostTagAuthorRepository::updateAuthors($post, [$owner->id]);

        }

    }

}