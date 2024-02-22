<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\Tag\TagVariantObject;
use App\Domains\Tag\TagRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleTagController extends Controller
{

    public function get(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'limit' => 'integer|max:100',
            'offset' => 'integer',
        ]);

        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset', 0);

        $tags = TagRepository::getTags($blog, $limit, $offset)
            ->map(fn ($tag) => new TagObject($tag, $blog));

        return response()->json($tags);
    }

    public function search(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'search' => 'required|string',
        ]);

        $search = (string) $request->string('search');

        $tags = TagRepository::searchTags($blog, $search, limit: 10)
            ->map(fn ($tag) => new TagObject($tag, $blog));

        return response()->json($tags);
    }

    public function create(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $name = (string) $request->string('name');

        $tag = TagRepository::createTag($blog, $name);

        return response()->json(new TagObject($tag, $blog));
    }

    public function update(Request $request, Tag $tag, Blog $blog) : JsonResponse
    {
        $validates = [
            'slug' => 'string',
            'code_head' => 'string|nullable',
            'code_foot' => 'string|nullable',
        ];

        $request->validate($validates);

        $updatables = array_keys($validates);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $tag = TagRepository::updateTag($tag, $updates);

        return response()->json(new TagObject($tag, $blog));
    }

    public function delete(Tag $tag) : JsonResponse
    {
        TagRepository::deleteTag($tag);

        return response()->json();
    }

    public function checkSlugAvailability(Request $request, Blog $blog, Tag $tag) : JsonResponse
    {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $slug = (string) $request->string('slug');

        $slugTag = TagRepository::getTagByBlogIdAndSlug($blog->id, $slug);

        return response()->json([
            'available' => $slugTag === null || $slugTag->id === $tag->id,
        ]);
    }

    public function createVariant(Blog $blog, Tag $tag, Language $language) : JsonResponse
    {
        $variant = TagRepository::createTagVariant($tag, $language);

        return response()->json(new TagVariantObject($variant, $tag, $blog));
    }

    public function updateVariant(Request $request, Blog $blog, Tag $tag, Language $language) : JsonResponse
    {
        $variant = TagRepository::getTagVariantByTagIdAndLanguageId($tag->id, $language->id);

        if (! $variant) {
            throw new TrustedException('Variant not found');
        }

        $validates = [
            'name' => 'string|nullable|max:255',
            'description' => 'string|nullable|max:255',
        ];

        $request->validate($validates);

        $updatables = array_keys($validates);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $variant = TagRepository::updateTagVariant($variant, $updates);

        return response()->json(new TagVariantObject($variant, $tag, $blog));
    }

    public function deleteVariant(Tag $tag, Language $language) : JsonResponse
    {
        if ($language->is_primary) {
            throw new TrustedException(
                'Primary language variant cannot be deleted. Delete the tag instead',
                TrustedException::ERROR_UNPROCESSABLE
            );
        }

        $variant = TagRepository::getTagVariantByTagIdAndLanguageId($tag->id, $language->id);

        if (! $variant) {
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);
        }

        TagRepository::deleteTagVariant($variant);

        return response()->json();
    }
}
