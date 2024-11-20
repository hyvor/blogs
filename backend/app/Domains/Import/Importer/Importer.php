<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\Content\Nodes\Audio\Audio;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\PostRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use Hyvor\Phrosemirror\Document\Node;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function() {
            $this->importPosts();
        });
    }

    private function importPosts() : void
    {

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($this->blog);
        $owner = UserRepository::getOwnerOfBlog($this->blog);

        // echo('Importing posts' . "\n");

        foreach ($this->parser->posts as $importingPost)
        {

            // echo('Importing post (' . ($this->postsCount + 1) . ')' . $importingPost->variants[0]->slug . "\n");

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

                $content = $this->importMediaOfContent($importingVariant->content);

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

    private function importMediaOfContent(string $content) : string
    {

        if (!$this->importImages)
            return $content;

        $document = PostContentService::getDocumentFromJson($content, $this->blog);

        $document->traverse(function (Node $node) {

            if (
                $node->isOfType(Image::class) ||
                $node->isOfType(Audio::class)
            ) {

                $src = strval($node->attr('src'));

                if (
                    $src &&
                    (
                        str_starts_with($src, 'http') ||
                        str_starts_with($src, 'file://')
                    )
                ) {
                    $node->attrs->set('src', $this->tryToUploadImage($src));
                }

            }

        });

        return $document->toJson();

    }

    private function tryToUploadImage(string $url) : ?string
    {

        if (str_starts_with($url, 'file://'))
            return $this->uploadLocalImage($url);

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

    private function uploadLocalImage(string $localUrl) : ?string
    {
        $path = str_replace('file://', '', $localUrl);
        if (!file_exists($path))
            return null;

        $media = app(MediaRepository::class);
        try {
            $image = $media->uploadFromLocal($this->blog, $path);
        } catch (UploadException) {
            $image = null;
        }
        if ($image) {
            return PermalinkRepository::getMediaPermalink(
                $image,
                $this->blog
            );
        }
        return null;
    }

}