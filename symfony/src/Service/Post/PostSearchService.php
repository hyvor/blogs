<?php

namespace App\Service\Post;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class PostSearchService
{
    private const DEFAULT_REGCONFIG = 'simple';

    /**
     * Only the PostgreSQL default regconfigs are supported.
     * language code => regconfig
     * @var array<string, string>
     */
    private const REGCONFIG_MAP = [
        'en' => 'english',
        'ar' => 'arabic',
        'hy' => 'armenian',
        'eu' => 'basque',
        'ca' => 'catalan',
        'da' => 'danish',
        'nl' => 'dutch',
        'fi' => 'finnish',
        'fr' => 'french',
        'de' => 'german',
        'el' => 'greek',
        'hi' => 'hindi',
        'hu' => 'hungarian',
        'id' => 'indonesian',
        'ga' => 'irish',
        'it' => 'italian',
        'lt' => 'lithuanian',
        'ne' => 'nepali',
        'no' => 'norwegian',
        'pt' => 'portuguese',
        'ro' => 'romanian',
        'ru' => 'russian',
        'sr' => 'serbian',
        'es' => 'spanish',
        'sv' => 'swedish',
        'ta' => 'tamil',
        'tr' => 'turkish',
        'yi' => 'yiddish',
    ];

    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @return array{posts: Post[], total: int}
     */
    public function search(
        Blog $blog,
        Language $language,
        string $search,
        int $limit,
        int $offset,
        bool $isPage = false,
        ?bool $isPublished = true,
    ): array {
        $searchQuery = $this->getSearchQuery($search);
        $isPageSql = $isPage ? 'true' : 'false';

        $where = "pv.calculated_ts @@ to_tsquery(pv.ts_language, :query)
            AND pv.language_id = :language
            AND p.blog_id = :blog
            AND p.is_page = $isPageSql";

        if ($isPublished === true) {
            $where .= " AND pv.status = 'published'";
        }

        $params = [
            'query' => $searchQuery,
            'language' => $language->getId(),
            'blog' => $blog->getId(),
        ];

        $totalFetch = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM post_variants pv JOIN posts p ON p.id = pv.post_id WHERE $where",
            $params
        );
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return ['posts' => [], 'total' => 0];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT pv.post_id FROM post_variants pv JOIN posts p ON p.id = pv.post_id WHERE $where
            ORDER BY ts_rank(pv.calculated_ts, to_tsquery(pv.ts_language, :query)) DESC
            LIMIT :limit OFFSET :offset",
            array_merge($params, [
                'limit' => $limit,
                'offset' => $offset,
            ])
        );

        /** @var array<int|string> $ids */
        $ids = array_column($rows, 'post_id');

        if (empty($ids)) {
            return ['posts' => [], 'total' => $total];
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['id' => $ids]);

        /** @var array<int|string, int> $idOrder */
        $idOrder = array_flip($ids);
        usort($posts, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) <=> ($idOrder[$b->getId()] ?? 0));

        return ['posts' => $posts, 'total' => $total];
    }

    /**
     * Converts a search string (usually from a user) into a query that can be used with tsquery
     */
    public function getSearchQuery(string $search): string
    {
        // replace special characters
        $replaced = (string)preg_replace('/[*:|&!()]/', '', $search);
        $replaced = (string)preg_replace('/\s+/', ':* | ', $replaced);
        return $replaced . ':*';
    }

    public function findClosestRegconfigByLanguageCode(?string $languageCode): string
    {
        if (!$languageCode) {
            return self::DEFAULT_REGCONFIG;
        }

        $languageCode = strtolower($languageCode);

        foreach (self::REGCONFIG_MAP as $key => $value) {
            $firstTwoChars = substr($languageCode, 0, 2);
            if ($firstTwoChars === $key) {
                return $value;
            }
        }

        return self::DEFAULT_REGCONFIG;
    }
}
