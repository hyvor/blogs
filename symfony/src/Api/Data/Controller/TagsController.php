<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\TagObjectFactory;
use App\Api\Data\Input\GetTagInput;
use App\Api\Data\Input\GetTagsInput;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Entity\Tag;
use App\Service\Tag\TagService;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TagsController
{
    public const ALLOWED_SORTS = [
        'posts_count' => 't.posts_count',
        'created_at' => 't.created_at',
    ];

    public function __construct(
        private DataApiHelper $dataApiHelper,
        private TagObjectFactory $tagObjectFactory,
        private TagService $tagService,
    ) {}

    #[Route('/tag', name: 'tag', methods: ['GET'])]
    public function tag(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetTagInput $input): JsonResponse
    {
        if ($input->id === null && $input->slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        $language = $this->dataApiHelper->getLanguage($blog, $input->language);

        $tag = null;
        if ($input->id !== null) {
            $tag = $this->tagService->getTagById($blog, $input->id);
        } elseif ($input->slug !== null) {
            $tag = $this->tagService->getTagBySlug($blog, $input->slug);
        }

        if ($tag === null) {
            throw new NotFoundHttpException('Tag not found');
        }

        $tagObject = $this->tagObjectFactory->create($tag, $blog, $language);

        $filtered = KeysFilter::filter($tagObject, $input->keys);

        return new JsonResponse($filtered);
    }

    #[Route('/tags', name: 'tags', methods: ['GET'])]
    public function tags(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetTagsInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $limit = $this->dataApiHelper->getLimit($input->limit);
        $page = $this->dataApiHelper->getPage($input->page);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort($input->sort, self::ALLOWED_SORTS);

        try {
            $result = $this->tagService->getTagsWithFilterQ($blog, $input->filter, $limit, $offset, $orderBys, $input->visibility);
        } catch (FilterQException $e) {
            throw new UnprocessableEntityHttpException('Filter error: ' . $e->getMessage(), $e);
        }

        $tagObjects = array_map(
            fn(Tag $tag) => $this->tagObjectFactory->create($tag, $blog, $language),
            $result['tags']
        );

        $filteredTags = KeysFilter::filter($tagObjects, $input->keys);

        return new JsonResponse([
            'data' => $filteredTags,
            'pagination' => new PaginationObject($limit, $page, $result['total']),
        ]);
    }
}
