<?php

namespace App\Domains\Import\HyvorBlogs;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\MediaAwareParserAbstract;
use App\Domains\Import\Importer\ParserException;
use App\Models\Blog;
use Carbon\Carbon;

/**
 * This is TODO: DO NOT USE YET
 */
class HyvorBlogsParser extends MediaAwareParserAbstract
{

    /**
     * @var array<mixed>
     */
    private array $data;

    public function __construct(
        private Blog $blog,

        // where data is at
        // - export.json
        // - media/ (TODO)
        string $path,

        private JobMessageLog $log,
    ) {
        $exportFile = $path . '/export.json';

        if (!file_exists($exportFile)) {
            throw new ParserException('Export file not found');
        }

        $json = file_get_contents($exportFile);

        if ($json === false) {
            throw new ParserException('Could not read export file');
        }

        $data = json_decode($json, true);

        if ($data === null) {
            throw new ParserException('Could not parse export file');
        }

        $this->data = $data;
        // $this->blogUrl = PermalinkRepository::getBaseUrl($this->blog);
    }

    public function parse(): void
    {
        $posts = $this->data['posts'];

        foreach ($posts as $post) {
            $variant = $post['variants'][0];

            $publishedAt = $post['published_at'];

            if ($publishedAt === null) {
                $this->log->info('Skipping post with no published_at: ' . $variant['slug']);
                continue;
            }

            $this->addPost(
                new ImportingPost(
                    publishedAt: Carbon::createFromTimestamp($post['published_at']),
                    isPage: $post['is_page'],
                    featuredImageUrl: $post['featured_image_url'],
                    variants: [
                        new ImportingPostVariant(
                            slug: $variant['slug'],
                            content: $variant['content'],
                            title: $variant['title'] ?? '',
                            description: $variant['description'] ?? '',
                        )
                    ]
                )
            );
        }
    }

    public function getMissingUploadsCount(): int
    {
        return 0;
    }

}