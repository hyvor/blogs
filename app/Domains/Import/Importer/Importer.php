<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use Hyvor\Phrosemirror\Document\Node;

class Importer
{

    public int $postsCount = 0;

    public function __construct(
        private readonly Blog $blog,
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

            $featuredImageUrl = $importingPost->featuredImageUrl ?
                $this->tryToUploadImage($importingPost->featuredImageUrl) :
                null;

            $post = PostRepository::createPost(
                $this->blog,
                [
                    'published_at' => $importingPost->publishedAt,
                    'featured_image_url' => $featuredImageUrl,
                    'is_page' => $importingPost->isPage,
                    'is_featured' => $importingPost->isFeatured
                ]
            );

            foreach ($importingPost->variants as $importingVariant) {

                $content = $this->importImagesOfContent($importingVariant->content);

                $variant = PostRepository::getPostVariantByPostIdAndLanguageId(
                    $post->id,
                    $primaryLanguage->id
                );

                if (!$variant)
                    continue;

                PostRepository::updatePostVariant($variant, [
                    'slug' => $importingVariant->slug,
                    'title' => $importingVariant->title,
                    'description' => $importingVariant->description,
                    'content' => $content,
                    'status' => $importingVariant->status
                ]);

            }

            if ($owner) {
                PostTagAuthorRepository::updateAuthors($post, [$owner->id]);
            }

            $this->postsCount++;
        }

    }

    private function importImagesOfContent(string $content) : string
    {

        if (!$this->importImages)
            return $content;

        $document = PostContentService::getDocumentFromJson($content, $this->blog);

        $document->traverse(function (Node $node) {

            if ($node->isOfType(Image::class)) {

                $src = strval($node->attr('src'));

                if ($src && str_starts_with($src, 'http')) {
                    $node->attrs->set('src', $this->tryToUploadImage($src));
                }

            }

        });

        return $document->toJson();

    }

    private function tryToUploadImage(string $url) : string
    {
        if (!$this->importImages)
            return $url;

        $media = app(MediaRepository::class);
        try {
            $image = $media->uploadFromUrl($this->blog, $url);
        } catch (UploadException) {
            $image = null;
        }
        if ($image) {
            return PermalinkRepository::getMediaPermalink(
                $image,
                $this->blog
            );
        }
        return $url;
    }

}