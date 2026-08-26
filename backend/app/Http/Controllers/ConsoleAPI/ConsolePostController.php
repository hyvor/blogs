<?php

declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Post\PostRepository;
use App\Domains\Post\SlugValidationService;
use App\Http\Controllers\Controller;

class ConsolePostController extends Controller
{

    public function updatePostVariant(
        Request $request,
        Blog $blog,
        Post $post,
        SlugValidationService $slugValidationService
    ): JsonResponse {
        $request->validate([
            'language_id' => 'required|integer',
            'slug' => 'string|max:255|nullable',
            'status' => 'string|in:draft,published,scheduled',
            'content' => ['string', 'nullable', new ProsemirrorJsonRule],
            'content_unsaved' => ['string', 'nullable', new ProsemirrorJsonRule],
            'title' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',

            'seo_primary_keyword' => 'string|max:255|nullable',
            'seo_secondary_keywords' => 'array',
            'seo_secondary_keywords.*' => 'string',
            'redirect_on_slug_change' => 'boolean',
        ]);

        $languageId = $request->integer('language_id');
        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (!$language) {
            throw new TrustedException('Language not found', TrustedException::ERROR_UNPROCESSABLE);
        }

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $languageId);

        if (!$variant) {
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        $variantUpdates = [];
        $redirectOnSlugChange = false;

        if ($request->has('slug')) {
            $variantUpdates['slug'] = $request->input('slug') !== null ?
                (string)$request->string('slug') :
                null;
            $redirectOnSlugChange = $variantUpdates['slug'] && $request->boolean('redirect_on_slug_change');
        }

        if ($request->has('status')) {
            $variantUpdates['status'] = PostStatusEnum::from((string)$request->string('status'));
        }

        if ($request->has('content')) {
            $variantUpdates['content'] = $request->input('content') !== null ?
                (string)$request->string('content') :
                null;
        }

        if ($request->has('content_unsaved')) {
            $variantUpdates['content_unsaved'] = $request->input('content_unsaved') !== null ?
                (string)$request->string('content_unsaved') :
                null;
        }

        if ($request->has('title')) {
            $variantUpdates['title'] = $request->input('title') !== null ?
                (string)$request->string('title') :
                null;
        }

        if ($request->has('description')) {
            $variantUpdates['description'] = $request->input('description') !== null ?
                (string)$request->string('description') :
                null;
        }

        if ($request->has('seo_primary_keyword')) {
            $variantUpdates['seo_primary_keyword'] = $request->input('seo_primary_keyword') !== null ?
                (string)$request->string('seo_primary_keyword') :
                null;
        }

        if ($request->has('seo_secondary_keywords')) {
            /** @var string[] $secondaryKeywords */
            $secondaryKeywords = (array)$request->input('seo_secondary_keywords');
            $variantUpdates['seo_secondary_keywords'] = $secondaryKeywords;
        }

        if (count($variantUpdates) > 0) {
            if (array_key_exists('slug', $variantUpdates) && $variantUpdates['slug'] !== null) {
                $bySlugPost = PostRepository::getPostByLanguageAndSlug($language, $variantUpdates['slug']);

                if ($bySlugPost && $bySlugPost->id !== $post->id) {
                    throw new TrustedException('Slug has already been taken');
                }

                $invalidCharacter = $slugValidationService->getFirstInvalidCharacter($variantUpdates['slug']);
                if ($invalidCharacter) {
                    throw new TrustedException('Slug cannot contain ' . $invalidCharacter);
                }

                $newPath = PermalinkRepository::getPostVariantPermalink(
                    $blog,
                    $variant,
                    customVariantSlug: $variantUpdates['slug']
                );
            } else {
                $newPath = null;
            }

            $oldPath = PermalinkRepository::getPostVariantPermalink($blog, $variant, onlyPath: true);

            PostRepository::updatePostVariant($variant, $variantUpdates);

            if ($redirectOnSlugChange && $oldPath && $newPath && $oldPath !== $newPath) {
                $redirect = RedirectRepository::getRedirectByPath($blog, $oldPath);

                if ($redirect) {
                    RedirectRepository::updateRedirect($redirect, [
                        'to' => $newPath
                    ]);
                } else {
                    RedirectRepository::createRedirect(
                        $blog,
                        false,
                        $oldPath,
                        $newPath,
                        RedirectTypeEnum::PERMANENT
                    );
                }
            }
        }

        $variant->refresh();

        // update post and refresh variants
        $post->refresh();

        return response()->json(
            new PostVariantObject($variant, $post, $blog)
        );
    }

}
