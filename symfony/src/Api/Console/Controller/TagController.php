<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Tag\CheckTagSlugAvailableInput;
use App\Api\Console\Input\Tag\CreateTagInput;
use App\Api\Console\Input\Tag\CreateTagVariantInput;
use App\Api\Console\Input\Tag\DeleteTagVariantInput;
use App\Api\Console\Input\Tag\GetTagsInput;
use App\Api\Console\Input\Tag\UpdateTagInput;
use App\Api\Console\Input\Tag\UpdateTagVariantInput;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\TagVariantObject;
use App\Api\Console\Object\TagVariantObjectFactory;
use App\Entity\Tag;
use App\Service\Language\LanguageService;
use App\Service\Tag\TagService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TagController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private TagService $tagService,
        private LanguageService $languageService,
        private TagObjectFactory $tagObjectFactory,
        private TagVariantObjectFactory $tagVariantObjectFactory,
    ) {}

    #[Route('/tags', methods: ['GET'])]
    #[ScopeRequired(Scope::TAGS_READ)]
    #[OA\Get(
        description: 'Returns the tags of the blog, optionally filtered by a search term.',
        summary: 'List tags',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of tags.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: TagObject::class))),
    )]
    public function list(
        #[MapQueryString] GetTagsInput $input = new GetTagsInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $tags = $this->tagService->getTags($blog, $input->limit, $input->offset, $input->search);

        return new JsonResponse(array_map(
            fn($tag) => $this->tagObjectFactory->create($tag, $blog),
            $tags,
        ));
    }

    #[Route('/tag', methods: ['POST'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Post(
        description: 'Creates a tag with a variant in the primary language.',
        summary: 'Create a tag',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created tag.',
        content: new Model(type: TagObject::class),
    )]
    public function create(
        #[MapRequestPayload] CreateTagInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $tag = $this->tagService->createTag($blog, $input->name, $input->is_private);

        return new JsonResponse($this->tagObjectFactory->create($tag, $blog), 201);
    }

    #[Route('/tag/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Patch(
        description: 'Updates the settings of a tag that are shared across all languages. Only the provided fields are updated.',
        summary: 'Update a tag',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated tag.',
        content: new Model(type: TagObject::class),
    )]
    public function update(
        #[MapBlogEntity] Tag $tag,
        #[MapRequestPayload] UpdateTagInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $updates = [];
        if ($input->is_private !== null) {
            $updates['is_private'] = $input->is_private;
        }
        if ($input->slug !== null) {
            $updates['slug'] = $input->slug;
        }
        if ($input->code_head !== null) {
            $updates['code_head'] = $input->code_head;
        }
        if ($input->code_foot !== null) {
            $updates['code_foot'] = $input->code_foot;
        }

        $tag = $this->tagService->updateTag($tag, $updates);

        return new JsonResponse($this->tagObjectFactory->create($tag, $blog));
    }

    #[Route('/tag/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Delete(
        description: 'Deletes a tag and removes it from all posts.',
        summary: 'Delete a tag',
    )]
    #[OA\Response(response: 200, description: 'The tag was deleted.')]
    public function delete(#[MapBlogEntity] Tag $tag): JsonResponse
    {
        $this->tagService->deleteTag($tag);

        return new JsonResponse();
    }

    #[Route('/tag/{id}/slug-available', methods: ['GET'])]
    #[ScopeRequired(Scope::TAGS_READ)]
    #[OA\Get(
        description: 'Checks whether a slug can be used by the tag.',
        summary: 'Check tag slug availability',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns whether the slug is available.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'available', type: 'boolean')]),
    )]
    public function checkSlugAvailability(
        #[MapBlogEntity] Tag $tag,
        #[MapQueryString] CheckTagSlugAvailableInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $slugTag = $this->tagService->getTagBySlug($blog, $input->slug);

        return new JsonResponse([
            'available' => $slugTag === null || $slugTag->getId() === $tag->getId(),
        ]);
    }

    #[Route('/tag/{id}/variant', methods: ['POST'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Post(
        description: 'Creates the translation (variant) of a tag for a language.',
        summary: 'Create a tag variant',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created variant.',
        content: new Model(type: TagVariantObject::class),
    )]
    public function createVariant(
        #[MapBlogEntity] Tag $tag,
        #[MapRequestPayload] CreateTagVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->tagService->createTagVariant($tag, $language);

        return new JsonResponse($this->tagVariantObjectFactory->create($variant, $tag, $blog), 201);
    }

    #[Route('/tag/{id}/variant', methods: ['PATCH'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Patch(
        description: 'Updates the name and description of a tag variant.',
        summary: 'Update a tag variant',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated variant.',
        content: new Model(type: TagVariantObject::class),
    )]
    public function updateVariant(
        #[MapBlogEntity] Tag $tag,
        #[MapRequestPayload] UpdateTagVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->tagService->getTagVariant($tag, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $variant = $this->tagService->updateTagVariant($variant, $input->name, $input->description);

        return new JsonResponse($this->tagVariantObjectFactory->create($variant, $tag, $blog));
    }

    #[Route('/tag/{id}/variant', methods: ['DELETE'])]
    #[ScopeRequired(Scope::TAGS_WRITE)]
    #[OA\Delete(
        description: 'Deletes the translation (variant) of a tag for a language. The primary language variant cannot be deleted.',
        summary: 'Delete a tag variant',
    )]
    #[OA\Response(response: 200, description: 'The variant was deleted.')]
    public function deleteVariant(
        #[MapBlogEntity] Tag $tag,
        #[MapRequestPayload] DeleteTagVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException(
                'Primary language variant cannot be deleted. Delete the tag instead',
            );
        }

        $variant = $this->tagService->getTagVariant($tag, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $this->tagService->deleteTagVariant($variant);

        return new JsonResponse();
    }
}
