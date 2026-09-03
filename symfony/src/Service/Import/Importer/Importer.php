<?php

namespace App\Service\Import\Importer;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Media\MediaException;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\User\UserService;
use Doctrine\DBAL\Connection;
use Hyvor\Phrosemirror\Document\Node;

class Importer
{
    public int $postsCount = 0;

    public function __construct(
        private readonly Blog $blog,
        private readonly ParserAbstract $parser,
        private readonly bool $importImages,
        private readonly Connection $connection,
        private readonly LanguageService $languageService,
        private readonly PostService $postService,
        private readonly MediaService $mediaService,
        private readonly PermalinkService $permalinkService,
        private readonly PostContentService $postContentService,
    ) {}

    public function import(): void
    {
        $this->parser->parse();

        $this->connection->transactional(function () {
            $this->importPosts();
        });
    }

    private function importPosts(): void
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($this->blog);

        foreach ($this->parser->posts as $importingPost) {
            $featuredImageUrl = $importingPost->featuredImageUrl
                ? $this->tryToUploadImage($importingPost->featuredImageUrl)
                : null;

            $post = $this->postService->createPost(
                $this->blog,
                isPage: $importingPost->isPage,
                isFeatured: $importingPost->isFeatured,
                featuredImageUrl: $featuredImageUrl,
                publishedAt: $importingPost->publishedAt
            );

            foreach ($importingPost->variants as $importingVariant) {
                $content = $this->importMediaOfContent($importingVariant->content);

                $variant = $this->postService->getPostVariantByPostAndLanguage($post, $primaryLanguage);

                if (!$variant) {
                    continue;
                }

                $variant->setContentUnsaved($content);

                $this->postService->updatePostVariant($variant, $this->blog, [
                    'slug' => $importingVariant->slug,
                    'title' => $importingVariant->title,
                    'description' => $importingVariant->description,
                ]);

                if ($importingVariant->status === PostVariantStatus::PUBLISHED) {
                    $this->postService->publishPostVariant($this->blog, $variant);
                    $this->postService->updatePostVariant($variant, $this->blog, [
                        'published_at' => $importingPost->publishedAt,
                        'content_updated_at' => $importingPost->publishedAt,
                    ]);
                }
            }

            // imported posts won't have authors
            // we might want to change this later to import authors as well
            // if ($owner) {
            //     $this->postService->setPostAuthors($post, [$owner], flush: true);
            // }

            $this->postsCount++;
        }
    }

    private function importMediaOfContent(string $content): string
    {
        if (!$this->importImages) {
            return $content;
        }

        $document = $this->postContentService->getDocumentFromJson($content, $this->blog);

        $document->traverse(function (Node $node) {
            if (
                $node->isOfType(Image::class) ||
                $node->isOfType(Audio::class)
            ) {
                $src = $node->attr('src');
                $src = is_string($src) ? $src : '';

                if (
                    $src &&
                    (str_starts_with($src, 'http') || str_starts_with($src, 'file://'))
                ) {
                    $node->attrs->set('src', $this->tryToUploadImage($src));
                }
            }
        });

        return $document->toJson();
    }

    private function tryToUploadImage(string $url): ?string
    {
        if (str_starts_with($url, 'file://')) {
            return $this->uploadLocalImage($url);
        }

        if (!$this->importImages) {
            return $url;
        }

        try {
            $media = $this->mediaService->uploadFromUrl($this->blog, $url);
        } catch (MediaException) {
            $media = null;
        }

        if ($media) {
            return $this->permalinkService->getMediaPermalink($media, $this->blog);
        }

        return $url;
    }

    private function uploadLocalImage(string $localUrl): ?string
    {
        $path = substr($localUrl, strlen('file://'));
        if (!file_exists($path)) {
            return null;
        }

        try {
            $media = $this->mediaService->uploadFromLocalPath($this->blog, $path);
        } catch (MediaException) {
            $media = null;
        }

        if ($media) {
            return $this->permalinkService->getMediaPermalink($media, $this->blog);
        }

        return null;
    }
}
